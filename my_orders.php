<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
if (!isset($_SESSION['user_id']) &&!isset($_SESSION['user']) &&!isset($_SESSION['loggedin'])) { header('Location: login.php'); exit(); }
$user_id = (int)($_SESSION['user_id']?? $_SESSION['user']['id']?? 0);
$is_pdo = $conn instanceof PDO;
try {
    if($is_pdo){
        $conn->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS tracking_number VARCHAR(50)");
        $conn->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS courier VARCHAR(50) DEFAULT 'BUKID2BAYAN Express'");
        $conn->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS farmer_lat DECIMAL(10,7) DEFAULT 14.3320");
        $conn->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS farmer_lng DECIMAL(10,7) DEFAULT 121.0850");
    } else {
        $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS tracking_number VARCHAR(50)");
        $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS courier VARCHAR(50) DEFAULT 'BUKID2BAYAN Express'");
        $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS farmer_lat DECIMAL(10,7) DEFAULT 14.3320");
        $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS farmer_lng DECIMAL(10,7) DEFAULT 121.0850");
    }
} catch(Exception $e){}
$filter = $_GET['filter']?? 'all';
include 'header.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
.orders-wrap{ max-width:1120px; margin:20px auto 40px auto; padding:0 16px; }
@media(max-width:600px){.orders-wrap{ margin-top:10px; padding:0 12px; } }
</style>
<div class="orders-wrap">
  <div style="background:#fff; border:1px solid #e9e9e9; border-radius:16px; padding:22px; margin-bottom:12px;">
    <h1 style="font-size:clamp(1.4rem,4vw,1.8rem); font-weight:900; margin:0;">My Orders - Tracking</h1>
    <p style="color:#666; font-size:0.9rem; margin:6px 0 0 0;">Track mo dito parang Shopee / Lazada + Google Maps</p>
  </div>
  <div style="background:#fff; border:1px solid #e9e9e9; border-radius:12px; padding:10px 12px; margin-bottom:12px; display:flex; gap:8px; flex-wrap:wrap; overflow-x:auto;">
    <?php $tabs=['all'=>'All','pending'=>'To Pay','to_ship'=>'To Ship','shipped'=>'Shipped','completed'=>'Completed']; foreach($tabs as $k=>$l){ $a=$filter==$k?'background:#111;color:#fff;':'background:#f5f5f5;color:#333;border:1px solid #eee;'; echo "<a href='my_orders.php?filter=$k' style='padding:7px 12px; border-radius:20px; text-decoration:none; font-weight:800; font-size:0.8rem; white-space:nowrap; $a'>$l</a>"; }?>
  </div>
  <?php
  $where = $filter=='all'? "" : "AND status='$filter'";
  if($filter=='pending') $where="AND status IN ('pending')";
  try {
      if($is_pdo){
          $stmt=$conn->prepare("SELECT * FROM orders WHERE user_id=? $where ORDER BY id DESC");
          $stmt->execute([$user_id]);
          $orders=$stmt->fetchAll(PDO::FETCH_ASSOC);
      } else {
          $res = $conn->query("SELECT * FROM orders WHERE user_id=$user_id $where ORDER BY id DESC");
          $orders=[];
          if($res){ while($r=$res->fetch_assoc()) $orders[]=$r; }
      }
      if(count($orders)==0){
          echo '<div style="background:#fff; border:1px solid #e9e9e9; border-radius:16px; padding:50px; text-align:center;"><p style="font-weight:800;">Wala pa order sa '.$filter.'</p><a href="products.php" style="background:#111; color:#fff; padding:10px 18px; border-radius:10px; text-decoration:none; font-weight:700; display:inline-block; margin-top:10px;">Mamili Na</a></div>';
      } else {
          foreach($orders as $order){
              $oid=$order['id']; $status=$order['status']??'pending';
              $color=$status=='pending'?'#ff9800':($status=='to_ship'?'#2196f3':($status=='shipped'?'#9c27b0':($status=='completed'?'#00b050':'#999')));
              $percent=['pending'=>20,'to_ship'=>45,'shipped'=>80,'completed'=>100][$status]??20;
 ?>
  <div style="background:#fff; border:1px solid #e9e9e9; border-radius:16px; padding:18px; margin-bottom:16px;">
    <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:8px;">
      <div><b>Order #<?= $oid?></b> <span style="background:<?= $color?>; color:#fff; padding:3px 10px; border-radius:20px; font-size:0.7rem; font-weight:800; text-transform:uppercase;"><?= htmlspecialchars($status)?></span>
        <?php if(!empty($order['tracking_number'])):?><span style="background:#111; color:#fff; padding:3px 8px; border-radius:8px; font-size:0.7rem; margin-left:4px;"><?= htmlspecialchars($order['tracking_number'])?> • <?= htmlspecialchars($order['courier'])?></span><?php endif;?>
        <div style="font-size:0.8rem; color:#666; margin-top:4px;"><?= $order['created_at']?> • <?= htmlspecialchars($order['payment_method']??'COD')?> • ₱<?= number_format($order['total_amount']??0,2)?></div>
        <div style="margin-top:8px; background:#f0f0f0; height:6px; border-radius:10px; width:260px; max-width:100%;"><div style="width:<?= $percent?>%; height:100%; background:#111; border-radius:10px;"></div></div>
      </div>
      <div style="text-align:right; font-size:0.85rem; color:#444;"><?= htmlspecialchars($order['address']??'')?><br><?= htmlspecialchars($order['phone']??'')?><br>
        <a href="order_tracking.php?id=<?= $oid?>" style="display:inline-block; margin-top:6px; background:#fff; border:1px solid #111; padding:6px 10px; border-radius:8px; text-decoration:none; font-weight:700; color:#111; font-size:0.8rem;">Full Tracking</a>
      </div>
    </div>
    <div style="margin-top:10px; border-top:1px solid #f0f0f0; padding-top:10px;">
      <?php
      try {
          if($is_pdo){
              $s=$conn->prepare("SELECT * FROM order_items WHERE order_id=?");
              $s->execute([$oid]);
              $items=$s->fetchAll(PDO::FETCH_ASSOC);
          } else {
              $ir=$conn->query("SELECT * FROM order_items WHERE order_id=$oid");
              $items=[]; if($ir){ while($it=$ir->fetch_assoc()) $items[]=$it; }
          }
          foreach($items as $it){ echo '<div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:4px;"><span>'.htmlspecialchars($it['product_name']).' x '.(int)$it['quantity'].'</span><span>₱'.number_format($it['price']*$it['quantity'],2).'</span></div>'; }
      } catch(Exception $e){}
     ?>
    </div>
    <?php if(in_array($status,['to_ship','shipped','completed'])):?>
    <div id="map-<?= $oid?>" style="height:240px; border-radius:12px; border:1px solid #e9e9e9; margin-top:12px;"></div>
    <div style="font-size:0.7rem; color:#666; margin-top:4px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:6px;">
      <span>🚚 <?= htmlspecialchars($order['courier']??'BUKID2BAYAN Express')?> • <?= $status=='shipped'?'On the way - Binan Laguna':'Preparing to ship'?></span>
      <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($order['address']??'')?>" target="_blank" style="color:#111; font-weight:700; text-decoration:none;">Open Google Maps</a>
    </div>
    <script>
    (function(){
      let farmer=[<?= $order['farmer_lat']??14.3320?>, <?= $order['farmer_lng']??121.0850?>];
      let map=L.map('map-<?= $oid?>').setView(farmer, 12);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(map);
      L.marker(farmer).addTo(map).bindPopup("Farmer Location");
      let addr="<?= addslashes($order['address']??'')?>";
      fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(addr)}&limit=1`)
    .then(r=>r.json()).then(d=>{
        if(d[0]){
          let cust=[parseFloat(d[0].lat), parseFloat(d[0].lon)];
          L.marker(cust).addTo(map).bindPopup("Delivery: <?= addslashes($order['address']??'')?>");
          L.polyline([farmer,cust],{color:'#111',dashArray:'8,8',weight:3}).addTo(map);
          map.fitBounds([farmer,cust],{padding:[20,20]});
          <?php if($status=='shipped'):?>
          let truck=L.marker(farmer,{icon:L.divIcon({html:'🚚',className:'',iconSize:[28,28]})}).addTo(map);
          let t=0; setInterval(()=>{ t+=0.008; if(t>1) t=0; truck.setLatLng([farmer[0]+(cust[0]-farmer[0])*t, farmer[1]+(cust[1]-farmer[1])*t]); }, 60);
          <?php endif;?>
        }
      });
    })();
    </script>
    <?php endif;?>
  </div>
  <?php } } } catch(Exception $e){ echo '<div style="background:#fff; padding:20px; border-radius:12px; text-align:center; color:red;">Error: '.htmlspecialchars($e->getMessage()).'</div>'; }?>
</div>
<?php include 'footer.php';?>

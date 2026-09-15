<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
include 'header.php';

$order_id = (int)($_GET['id']?? 0);
$user_id = (int)($_SESSION['user_id']?? 0);

// auto add columns & table kung wala
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS tracking_number VARCHAR(50)");
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS courier VARCHAR(50) DEFAULT ' BUKID2BAYAN Xpress'");
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS farmer_lat DECIMAL(10,7) DEFAULT 14.3320");
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS farmer_lng DECIMAL(10,7) DEFAULT 121.0850");
$conn->query("CREATE TABLE IF NOT EXISTS tracking_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT,
  status VARCHAR(30),
  location VARCHAR(255),
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$order = $conn->query("SELECT * FROM orders WHERE id=$order_id")->fetch_assoc();
if(!$order){ echo "<div style='margin:130px auto; text-align:center;'><h3>Order not found</h3><a href='my_orders.php'>Back</a></div>"; include 'footer.php'; exit(); }

$status = $order['status']?? 'pending';
$progress = ['pending'=>15,'to_pay'=>15,'to_ship'=>40,'shipped'=>75,'out_for_delivery'=>85,'completed'=>100,'cancelled'=>0];
$percent = $progress[$status]?? 15;
$tracking = $order['tracking_number']?? 'SPXPH'.rand(1000000000,9999999999);

// kung wala pang logs, auto gawa base sa status
$hasLogs = $conn->query("SELECT COUNT(*) as c FROM tracking_logs WHERE order_id=$order_id")->fetch_assoc()['c'];
if($hasLogs==0){
    if(function_exists('addTracking')){
        addTracking($conn,$order_id,'pending','Order Placed','Order placed by '. $order['customer_name']);
        if(in_array($status,['to_ship','shipped','completed'])) addTracking($conn,$order_id,'to_ship','Binan Farmer Centre','Seller confirmed order - preparing to ship');
        if(in_array($status,['shipped','completed'])) {
            addTracking($conn,$order_id,'shipped','Binan Sorting Hub','Parcel departed - tracking '.$tracking.' assigned to '.$order['courier']);
            addTracking($conn,$order_id,'shipped','Calamba Hub - In Transit','Parcel inbounded at logistics facility');
        }
        if($status=='completed') addTracking($conn,$order_id,'completed','Buyer Location - '.$order['address'],'Parcel delivered - received by '.$order['customer_name']);
    }
}
$logs = $conn->query("SELECT * FROM tracking_logs WHERE order_id=$order_id ORDER BY created_at ASC");
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div style="max-width:1120px; margin:110px auto 20px auto; padding:0 20px; display:grid; grid-template-columns:1fr 420px; gap:16px;">
  <div>
    <div style="background:#fff; border:1px solid #e9e9e9; border-radius:14px; padding:16px; margin-bottom:14px;">
        <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:8px;">
            <div><b>Order #<?= $order_id?></b> <span style="background:#111; color:#fff; padding:3px 10px; border-radius:10px; font-size:0.75rem; font-weight:800;"><?= htmlspecialchars($tracking)?></span><br>
            <span style="font-size:0.85rem; color:#555;"><?= htmlspecialchars($order['courier'])?> • <?= htmlspecialchars($order['payment_method'])?> • ₱<?= number_format($order['total_amount'],2)?></span><br>
            <span style="font-size:0.8rem; color:#666;"><?= htmlspecialchars($order['address'])?></span></div>
            <div style="text-align:right;"><span style="background:<?= $status=='shipped'?'#9c27b0':($status=='completed'?'#00b050':'#2a9d8f')?>; color:#fff; padding:5px 12px; border-radius:20px; font-size:0.75rem; font-weight:800; text-transform:uppercase;"><?= htmlspecialchars($status)?></span><br><span style="font-size:0.8rem;">ETA: 1-2 days</span><br><span style="font-size:0.7rem; color:#888;"><?= htmlspecialchars($order['phone'])?></span></div>
        </div>
        <div style="margin-top:14px; background:#f5f5f5; height:8px; border-radius:10px; overflow:hidden;"><div style="width:<?= $percent?>%; height:100%; background:#111; transition:width 0.5s;"></div></div>
        <div style="display:flex; justify-content:space-between; font-size:0.7rem; font-weight:700; margin-top:6px; color:#666;"><span>Ordered</span><span>To Ship</span><span>Shipped</span><span>Delivered</span></div>
    </div>

    <div id="map" style="height:480px; border-radius:14px; border:1px solid #e9e9e9;"></div>
    <p style="font-size:0.75rem; color:#888; margin-top:6px;">Live tracking parang Lazada LEX / Shopee SPX. Powered by OSM + Google Maps style.</p>
  </div>

  <div style="background:#fff; border:1px solid #e9e9e9; border-radius:14px; padding:16px; height:fit-content;">
    <h3 style="font-weight:900; margin:0 0 12px 0;">Shipment History</h3>
    <div style="border-left:3px solid #111; padding-left:12px;">
        <?php while($log = $logs->fetch_assoc()):?>
        <div style="margin-bottom:16px; position:relative;">
            <div style="width:10px; height:10px; background:<?= $log['status']=='completed'?'#00b050':($log['status']=='shipped'?'#9c27b0':'#111')?>; border-radius:50%; position:absolute; left:-18px; top:4px;"></div>
            <b style="text-transform:capitalize; font-size:0.9rem;"><?= htmlspecialchars($log['status'])?></b> - <span style="font-size:0.8rem; font-weight:700;"><?= htmlspecialchars($log['location'])?></span><br>
            <span style="font-size:0.8rem; color:#666;"><?= $log['created_at']?></span><br>
            <span style="font-size:0.85rem; color:#333;"><?= htmlspecialchars($log['description'])?></span>
        </div>
        <?php endwhile;?>
    </div>
    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($order['address'])?>" target="_blank" style="display:block; text-align:center; margin-top:14px; background:#fff; border:1.5px solid #111; padding:10px; border-radius:10px; text-decoration:none; font-weight:800; color:#111;"><i class="fas fa-map"></i> Open in Google Maps</a>
    <a href="my_orders.php" style="display:block; text-align:center; margin-top:8px; background:#111; color:#fff; padding:10px; border-radius:10px; text-decoration:none; font-weight:800;">Back to Orders</a>
  </div>
</div>

<script>
// --- MAP LOGIC - Shopee/Lazada style live truck ---
let farmer = [<?= $order['farmer_lat']?? 14.3320?>, <?= $order['farmer_lng']?? 121.0850?>];
let map = L.map('map').setView(farmer, 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19}).addTo(map);
L.marker(farmer).addTo(map).bindPopup("Farmer -  BUKID2BAYAN Hub - Binan").openPopup();

let address = "<?= addslashes($order['address'])?>";
fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
.then(r=>r.json()).then(data=>{
  if(data[0]){
    let cust = [parseFloat(data[0].lat), parseFloat(data[0].lon)];
    L.marker(cust).addTo(map).bindPopup("Buyer: <?= addslashes($order['customer_name'])?> - <?= addslashes($order['address'])?>");
    L.polyline([farmer, cust], {color:'#111', dashArray:'8,8', weight:3}).addTo(map);
    map.fitBounds([farmer, cust], {padding:[40,40]});

    <?php if($status=='shipped' || $status=='to_ship'):?>
    let truck = L.marker(farmer, {icon: L.divIcon({html:'🚚', className:'', iconSize:[32,32], iconAnchor:[16,16]})}).addTo(map);
    let t=0;
    setInterval(()=>{
        t+=0.008; if(t>1) t=0;
        let lat=farmer[0]+(cust[0]-farmer[0])*t;
        let lng=farmer[1]+(cust[1]-farmer[1])*t;
        truck.setLatLng([lat,lng]);
    }, 70);
    <?php endif;?>
  } else {
    // fallback kung di mahanap address
    let cust = [14.3400, 121.0800];
    L.marker(cust).addTo(map).bindPopup("Buyer approx");
    L.polyline([farmer, cust], {color:'#111', dashArray:'8,8', weight:3}).addTo(map);
  }
});
</script>
<?php include 'footer.php';?>
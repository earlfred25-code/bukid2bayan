</main>

<style>
.site-footer{
    background: linear-gradient(135deg, #1a2e1a 0%, #2d4a2d 100%);
    color:#e8f5e9;
    padding:50px 0 20px 0;
    margin-top:0;
    position:relative;
    z-index:10;
}
.site-footer .container{ max-width:1280px; margin:0 auto; padding:0 16px; }
.footer-main{
    display:grid;
    grid-template-columns:1fr;
    gap:30px;
    padding-bottom:30px;
}
.footer-col h3.footer-logo{
    font-weight:900;
    letter-spacing:2px;
    font-size:1.4rem;
    color:#fff;
    margin:0 0 12px 0;
}
.footer-col h4{
    font-weight:800;
    color:#a5d6a7;
    margin:0 0 14px 0;
    font-size:1rem;
    text-transform:uppercase;
    letter-spacing:1px;
}
.footer-col p{
    color:#c8e6c9;
    line-height:1.6;
    font-size:0.9rem;
    margin:0;
}
.footer-col ul{
    list-style:none;
    padding:0;
    margin:0;
}
.footer-col ul li{ margin-bottom:10px; }
.footer-col ul li a, .social-links a{
    color:#c8e6c9;
    text-decoration:none;
    font-size:0.9rem;
    transition:color 0.2s;
    display:inline-block;
}
.footer-col ul li a:hover, .social-links a:hover{ color:#fff; transform:translateX(3px); }
.social-links{ display:flex; flex-wrap:wrap; gap:12px; }
.social-links a{
    background:rgba(255,255,255,0.08);
    padding:6px 14px;
    border-radius:20px;
    border:1px solid rgba(255,255,255,0.1);
}
.social-links a:hover{ background:rgba(255,255,255,0.15); }
.footer-bottom{
    border-top:1px solid rgba(255,255,255,0.1);
    padding-top:20px;
    text-align:center;
}
.footer-bottom p{
    color:#81c784;
    font-size:0.85rem;
    margin:0;
}


@media(min-width:600px){
    .footer-main{ grid-template-columns:1fr 1fr; gap:30px 40px; }
}

@media(min-width:1024px){
    .footer-main{ grid-template-columns:1.5fr 1fr 1fr 1fr; }
}
</style>

<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <div class="footer-col">
                <h3 class="footer-logo">BUKID2BAYAN</h3>
                <p>Bridging the gap between dedicated farmers and conscious consumers with fresh, direct-trade produce.</p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">All Products</a></li>
                    <li><a href="cart.php">Your Cart</a></li>
                    <li><a href="login.php">Login / Register</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Support</h4>
                <ul>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Follow Us</h4>
                <div class="social-links">
                    <a href="#">Facebook</a>
                    <a href="#">Instagram</a>
                    <a href="#">Twitter</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> BUKID2BAYAN. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>

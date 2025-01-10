<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #ffb3c6, #f0e68c);
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .logo {
            font-size: 20px;
            font-weight: bold;
            color: #ff007f;
        }
        nav a {
            margin-right: 20px;
            text-decoration: none;
            color: #d36d8c;
            font-size: 18px;
            
            font-weight: bold;
        }
        nav a:hover {
            color: #ff007f;
        }
        .icons {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #ff007f;
        }
        .container {
            padding: 20px;
        }
        h1 {
            color: #ff007f;
            font-size: 24px;
        }
        .cart-items {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .cart-item {
            display: flex;
            align-items: center;
            border: 2px solid #ff007f;
            padding: 10px;
            background-color: #fff;
        }
        .cart-item img {
            width: 50px;
            height: 50px;
            margin-right: 10px;
        }
        .cart-item .details {
            flex: 1;
            font-size: 14px;
        }
        .cart-item input[type="checkbox"] {
            margin-right: 10px;
        }
        .search-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .search-bar input[type="text"] {
            padding: 5px;
            width: 200px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .search-bar button {
            border: none;
            background: transparent;
            cursor: pointer;
            margin-left: 5px;
        }
        .checkout-btn {
            display: block;
            margin: 30px auto;
            padding: 10px 20px;
            background-color: #ff007f;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 20px;
            cursor: pointer;
        }
        .checkout-btn:hover {
            background-color: #e60072;
        }
        .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 80px;
       
        top: 0;
        left: 0;
        right: 0;
        background-color: transparent;
        border-bottom: 2px solid rgb(223, 55, 83);
        z-index: 1000;
        padding: 0 20px;
    }
    
    .left-sec {
        display: flex;
        align-items: center;
    }
    .logo {
        height: 50px;
        width: 50px;
    }
    h1 {
        font-size: 18px;
        color: white;
        margin-left: 10px;
        line-height: 1.2;
    }
    span {
        font-size: 14px;
        color: rgb(223, 55, 83);
    }
    .mid-sec {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    width: 100%;
}
.feat {
    font-size: 18px;
    color: rgb(241, 56, 87);
    padding: 5px 10px;
    font-family: 'Georgia', serif;
    text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.384);
    margin-right: 30px;
    text-decoration: none;
    font-weight: 600;
    position: relative;
}

.feat:after {
    content: "";
    position: absolute;
    bottom: -3px;
    left: 0;
    height: 2px;
    width: 0;
    background-color: white;
    transition: width 0.3s ease;
}

.feat:hover {
    color:black;
}

.feat:hover:after {
    width: 100%;
}
.navbar {
    position: relative;
}
.dropdown {
    position: relative;
}

.dropdown .dropbtn {
    background-color: transparent;
    color: rgb(241, 56, 87);
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    font-weight: 600;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    min-width: 200px;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1;
}

.dropdown:hover .dropdown-content {
    display: block;
}

.dropdown-content a {
    color: rgb(223, 55, 83);
    padding: 10px 15px;
    font-family: 'Georgia', serif;
    text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.384);
    text-decoration: none;
    display: block;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.dropdown-content a:hover {
    background-color: rgb(223, 55, 83);
    color: white;
}

.printing-sub-dropdown, .imaging-sub-dropdown {
    display: none;
    position: absolute;
    left: 100%;
    top: 0;
    background-color: white;
    min-width: 160px;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 2;
}

.dropdown-content .dropdown:hover .printing-sub-dropdown,
.dropdown-content .dropdown:hover .imaging-sub-dropdown {
    display: block;
}

.printing-sub-dropdown a, .imaging-sub-dropdown a {
    padding: 10px 15px;
    color: rgb(223, 55, 83);
    text-decoration: none;
    display: block;
}

.printing-sub-dropdown a:hover, .imaging-sub-dropdown a:hover {
    background-color: rgb(223, 55, 83);
    color: white;
}
.right-sec {
    display: flex;
    align-items: center;
}
.logo-btn {
    height: 35px;
    width: 35px;
    margin-right: 20px;
    transition: transform 0.3s ease;
}

.logo-btn:hover {
    transform: scale(1.2);
}
.logo-btn {
        height: 35px;
        width: 35px;
        margin-right: 20px;
        transition: transform 0.3s ease;
    }

    .logo-btn:hover {
        transform: scale(1.2);
    }
    </style>
</head>
<body>
    <div class="header">
        <div class="left-sec">
            <img class="logo" src="../images/logo.png" alt="Logo">
            <h1>KRISHIEL <span><br>PRINTING AND IMAGING SERVICES</span></h1>
        </div>
        <div class="mid-sec">
            <a class="feat" href="user_dash.php">HOME</a>
            <div class="navbar">
                <div class="dropdown">
                    <button class="feat dropbtn">SERVICES</button>
                    <div class="dropdown-content">
                        <div class="dropdown">
                            <a href="#">Printing Services</a>
                            <div class="printing-sub-dropdown">
                                <a href="#">Print Document</a>
                                <a href="user_flyers.php">Flyer</a>
                                <a href="sticker.html">Stickers</a>
                                <a href="#">Invitation Cards</a>
                            </div>
                        </div>
                        <div class="dropdown">
                            <a href="#">Imaging Services</a>
                            <div class="imaging-sub-dropdown">
                                <a href="user_idpicture.php">ID Picture</a>
                                <a href="user_instax.html">Instax Photo</a>
                                <a href="user_sintra.html">Photo Sintra Board</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <a class="feat" href="user_aboutus.php">ABOUT US</a>
        </div>
        <div class="right-sec">
            <a href="#" onclick="viewCart()">
                <img class="logo-btn" src="../images/cart.png" alt="Cart">
            </a>
            <a href="#">
                <img class="logo-btn" src="../images/user.png" alt="User">
            </a>
        </div>
    </div>
    <div class="container">
        <h1>MY CART(7)</h1>
        <div class="search-bar">
            <input type="text" placeholder="Search...">
            <button>&#128269;</button>
        </div>
        <div class="cart-items">
            <!-- Cart Item -->
            <div class="cart-item">
                <input type="checkbox">
                <img src="https://via.placeholder.com/50" alt="Item">
                <div class="details">ORDER DETAILS</div>
            </div>
            <div class="cart-item">
                <input type="checkbox">
                <img src="https://via.placeholder.com/50" alt="Item">
                <div class="details">ORDER DETAILS</div>
            </div>
            <div class="cart-item">
                <input type="checkbox">
                <img src="https://via.placeholder.com/50" alt="Item">
                <div class="details">ORDER DETAILS</div>
            </div>
            <div class="cart-item">
                <input type="checkbox">
                <img src="https://via.placeholder.com/50" alt="Item">
                <div class="details">ORDER DETAILS</div>
            </div>
            <div class="cart-item">
                <input type="checkbox">
                <img src="https://via.placeholder.com/50" alt="Item">
                <div class="details">ORDER DETAILS</div>
            </div>
            <div class="cart-item">
                <input type="checkbox">
                <img src="https://via.placeholder.com/50" alt="Item">
                <div class="details">ORDER DETAILS</div>
            </div>
            <div class="cart-item">
                <input type="checkbox">
                <img src="https://via.placeholder.com/50" alt="Item">
                <div class="details">ORDER DETAILS</div>
            </div>
        </div>
        <button class="checkout-btn">CHECK OUT</button>
    </div>

    <!-- Modal (Payment Form) -->
    <div id="checkoutModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h2>Payment Form</h2>
            <form action="#" method="POST">
                <label for="fullname">FULL NAME</label>
                <input type="text" id="fullname" name="fullname" required>

                <label for="contactno">CONTACT NO.</label>
                <input type="text" id="contactno" name="contactno" required>

                <label for="address">ADDRESS</label>
                <input type="text" id="address" name="address" required>

                <h3>PAYMENT DETAILS</h3>
                <label for="shipping-subtotal">SHIPPING SUBTOTAL:</label>
                <input type="text" id="shipping-subtotal" name="shipping-subtotal" disabled>

                <label for="merchandise-subtotal">MERCHANDISE SUBTOTAL:</label>
                <input type="text" id="merchandise-subtotal" name="merchandise-subtotal" disabled>

                <label for="total-payment">TOTAL PAYMENT:</label>
                <input type="text" id="total-payment" name="total-payment" disabled>

                <button type="submit">PLACE ORDER</button>
            </form>
        </div>
    </div>

    <script>
    // Get the modal
    var modal = document.getElementById("checkoutModal");

    // Get the button that opens the modal
    var btn = document.querySelector(".checkout-btn");

    // Get the <span> element that closes the modal
    var span = document.querySelector(".close-btn");

    // When the user clicks the button, open the modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    function closeModal() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>

    <style>
    /* Modal background */
    .modal {
        display: none; 
        position: fixed; 
        z-index: 1; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        overflow: auto; 
        background-color: rgba(0, 0, 0, 0.4); 
        padding-top: 60px;
    }

    /* Modal content */
    .modal-content {
        background: linear-gradient(to right, #ffb3c6, #f0e68c);
        margin: 5% auto;
        padding: 50px;
        border-radius: 10px;
        width: 80%;
        max-width: 500px;
        position: relative;
    }

    /* Close button */
    .close-btn {
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 30px;
        font-weight: bold;
        color: #fff;
        cursor: pointer;
    }

    /* Form styling */
    .modal-content h2, .modal-content h3 {
        text-align: center;
        color: #ff3d78;
    }

    .modal-content label {
        display: block;
        margin: 10px 0 5px;
        color: #ff3d78;
    }

    .modal-content input[type="text"] {
        width: 100%;
        padding: 10px;
        margin: 5px 0 20px;
        border: 1px solid #ff3d78;
        border-radius: 5px;
    }

    .modal-content button {
        width: 105%;
        padding: 10px;
        background-color: #ff3d78;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .modal-content button:hover {
        background-color: #ff1e56;
    }
    </style>
</body>

</html>

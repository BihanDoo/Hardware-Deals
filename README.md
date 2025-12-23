# Hardware Deals
Want to see the website in action?   
visit: <a href="https://hardware-deals.ct.ws/">www.hardware-deals.ct.ws</a>


## Quick introduction
This is a website project, which is made for sellers to sell hardware items, like Ebay.  
it lets buyers to order/rent items from the sellers
> Note: This is a test website made for a project, not intended to be used in production.

## Features

### Create user accounts
anyone can create an account by clicking the profile icon in the index page  

### Switch to a seller profile
after creating, users can switch to a seller's profile from the profile page  
which enables the option to add new products
> Normal users can not add new products

### Add Products
Seller accounts have access to adding new products  
#### Adding Products
Products have different parameters like 
* title, description,   
* uploading multiple images, upload thumbnail (main image preview)
* Price and any offer tags
* categories (not fully implemented)
> When adding products, it automatically selects the storeID and records under that specific store
* select if the product is for rent
* select if pickup is available
* if in stock
* if wholesale (otherwise, only sell one item)
* if the price is not applicable, users can switch `Call for price` option
* any contact numbers

these parameters can be added when adding a new item
  after adding the item, the seller can view all products that they've added so far.
  


### Edit/Delete products
Sellers can edit products by visiting their profile  
  and also, sellers can delete listed products

### Search for products in the search bar
> feature only implemented in index.php, others left it for design improvements

### Cart
users can add items to cart (regardless of the account type)   
Users can remove any item, or change quantity  
Non-deliverable  (Eg: items that marked as for rent) can not be added into cart, instead, it shows call to action contact number.

### Checkout
Users can proceed to checkout, which lands into a order summary page.  
features are only implemented for cash on delivery method.  
users can add details and place order. which lands into an order tracking page with the orderID

### Order tracking
users can view the status and details of the order
when entering the orderID, it checks if the id belongs to the logged-in user.   
if yes, then it displays the status




### Misc

#### Edit profile
Users can edit their profile by visiting the profile page (adding a profile picture,... etc)

#### Add reviews with images
users can add reviews into products with images.


## About the code
Coded in HTML with PHP, uses MySQL as database
you can set up this system by cloning the project and uploading the sql to phpmyadmin, it creates all the necessary tables to get started with.




#### COPYRIGHT NOTICE  
Copyright © 2025 Bihandu Sathmira  
All rights reserved.  

This source code and associated documentation files (the “Software”)   
are the exclusive property of the copyright owner named above.  
and is protected by copyright law   
under the <a href="https://www.nipo.gov.lk/web/images/Act/IP-Act-AS.pdf">Intellectual Property Act, No. 36 of 2003 of Sri Lanka</a> ,  

Unauthorized copying, modification, distribution, adaptation, publication, disassembly,  
  or any other use of this Source code and/or Software (i.e. the Website),
  in whole or in part,
  without the express prior written permission of the copyright holder
  is strictly prohibited and may subject the infringer to civil and/or
  criminal penalties under applicable law.

  The copyright holder reserves all rights in and to the source code
  not expressly granted herein.

  For licensing inquiries, contact:
  sathmirabihandu@gmail.com


Free for Non-commercial use only







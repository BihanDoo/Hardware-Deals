alter TABLE stores add CONSTRAINT fkfk FOREIGN KEY(storeContactUEmail) REFERENCES users (uEmail)

CREATE TABLE `hardwaredeals`.`products` (`productID` INT NOT NULL AUTO_INCREMENT , `imgURL` VARCHAR(100) NOT NULL , `offTagDescription` VARCHAR(20) NULL , `oldPrice` DOUBLE NULL , `newPrice` DOUBLE NOT NULL , `title` VARCHAR(100) NOT NULL , `description` VARCHAR(500) NOT NULL , `soldByStoreID` INT NOT NULL , `reviewCount` INT NULL , `rating` FLOAT NULL , `deliveryAvailable` BOOLEAN NOT NULL DEFAULT TRUE , `callToAction` INT NULL DEFAULT NULL , `pickup` BOOLEAN NOT NULL DEFAULT TRUE , `inStock` BOOLEAN NOT NULL DEFAULT TRUE , `forRent` BOOLEAN NOT NULL DEFAULT FALSE , `wholesale` BOOLEAN NOT NULL DEFAULT TRUE , `searchTags` VARCHAR(500) NULL , PRIMARY KEY (`productID`)) ENGINE = MyISAM;


alter TABLE stores 
add CONSTRAINT fk_STORE_CONTACT_U_EMAIL FOREIGN KEY(storeContactUEmail) 
REFERENCES users (uEmail) ON DELETE CASCADE

FOREIGN KEY(storeContactUEmail) REFERENCES users(uEmail)

ALTER TABLE stores drop CONSTRAINT fk_STORE_CONTACT_U_EMAIL 

ALTER TABLE stores ADD CONSTRAINT fk_STORE_CONTACT_U_EMAIL FOREIGN KEY(storeContactUEmail) REFERENCES users(uEmail) ON DELETE CASCADE

<?php echo $row["Picture"] ; ?>

$sql = "SELECT * FROM `products` WHERE `email` = '".$_SESSION["userName"]."'";


WHERE `email` = '".$_SESSION["userName"]."'

A powerful cordless drill for all your home and professional needs. Includes battery, charger, and a bits set with a 90 degree bend.
Jaya hardwares! the all in one place for any hardware item you can never find on anywhere else
Jaya hardwares! the all in one place for large collection of hardware items that you can never find on anywhere else


INSERT INTO `stores` (`storeID`, `storeName`, `storeContactUEmail`, `storeBio`, `storeLocation`) VALUES (NULL, 'Jaya Stores', 'jaya@jayahardwares.com', 'Jaya hardwares! the all in one place for large collection of hardware items that you can never find on anywhere else', 'Colombo')


INSERT INTO `products` (`productID`, `imgURL`, `offTagDescription`, `oldPrice`, `newPrice`, `title`, `description`, `soldByStoreID`, `reviewCount`, `rating`, `deliveryAvailable`, `callToAction`, `pickup`, `inStock`, `forRent`, `wholesale`, `searchTags`) VALUES (NULL, 'drilldemo/drill1.jpg', '20% OFF', '8500', '7200', 'Cordless Drill', 'A powerful cordless drill for all your home and professional needs. Includes battery, charger, and a bits set with a 90 degree bend.', '1', NULL, NULL, '1', NULL, '1', '1', '0', '1', 'drill');


ALTER TABLE `users` ADD `isSeller` BOOLEAN NOT NULL DEFAULT FALSE AFTER `contact`;

ALTER TABLE users ADD isSeller BOOLEAN NOT NULL DEFAULT FALSE AFTER contact;
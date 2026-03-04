DELETE FROM `user`;

INSERT INTO `user` (`iduser`,`username`,`new_pass`,`isAdmin`,`suspended`,`name`,`failedloginattempts`,`email`,`title`) VALUES
(1,'ncarthy','$2y$10$hQvyTcHomPvsycZjE1WxEOwKxVLtYO7FM/YpueIKGrLO3mb8o74Wm',1,0,'Neil Carthy',0,'ncarthy@example.com','Mr'),
(20,'testuser','$2y$10$hQvyTcHomPvsycZjE1WxEOwKxVLtYO7FM/YpueIKGrLO3mb8o74Wm',0,0,'Test User',0,'testuser@example.com','Ms');

DELETE FROM `usertoken`;

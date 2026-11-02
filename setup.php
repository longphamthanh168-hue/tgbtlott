<?php // Chạy 1 lần: php setup.php rồi XÓA file này
require 'config.php';$h=password_hash('ChangeMe@123',PASSWORD_DEFAULT);
db()->prepare("INSERT INTO admins(username,password_hash,role)VALUES('admin',?,'super')")->execute([$h]);echo "OK\n";

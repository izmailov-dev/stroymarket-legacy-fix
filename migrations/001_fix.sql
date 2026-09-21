-- Индексы: главная причина 8 секунд
ALTER TABLE products ADD INDEX idx_cat_id (cat_id);
ALTER TABLE reviews  ADD INDEX idx_product_id (product_id);

-- Место под хеши паролей (сейчас 50 символов, хешу нужно 60+)
ALTER TABLE users MODIFY pass VARCHAR(255) NOT NULL;

-- MyISAM -> InnoDB: транзакции, блокировка строк, а не всей таблицы
ALTER TABLE categories ENGINE=InnoDB;
ALTER TABLE products   ENGINE=InnoDB;
ALTER TABLE reviews    ENGINE=InnoDB;
ALTER TABLE users      ENGINE=InnoDB;
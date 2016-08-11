/* Заявка на договор */
CREATE TABLE `app_contract` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL DEFAULT '',
  `status_id` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `posted_user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `pdate` bigint(20) unsigned NOT NULL DEFAULT '0',
  `txt` text NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `supplier_contact_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `supplier_contact_data_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `is_confirmed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_confirm_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `confirm_pdate` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `byuser_id` (`user_id`),
  KEY `code` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

/* Статус заявки на договор */
CREATE TABLE `app_contract_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

CREATE TABLE `app_contract_view` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `col_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ord` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `col_id` (`col_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

CREATE TABLE `app_contract_view_field` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `colname` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `colname` (`colname`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

CREATE TABLE `app_contract_file` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `history_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(512) NOT NULL DEFAULT '',
  `orig_name` varchar(512) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `by_history_id` (`history_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

CREATE TABLE `app_contract_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `app_contract_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `status_id` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `pdate` bigint(20) unsigned NOT NULL DEFAULT '0',
  `txt` text NOT NULL,
  `is_new` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `by_app_contract_id` (`app_contract_id`),
  KEY `by_user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

CREATE TABLE `app_contract_creation_session` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `login` varchar(255) NOT NULL DEFAULT '',
  `SID` varchar(255) NOT NULL DEFAULT '',
  `pdate` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ip` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251;

/* Добавление записей */
INSERT INTO `app_contract_status` (`id`, `name`, `description`) VALUES
(1, 'Создана', ''),
(2, 'Утверждена', ''),
(3, 'Аннулирована', ''),
(4, 'Не утверждена', '');

INSERT INTO `app_contract_view_field` (`id`, `name`, `colname`) VALUES
(1, 'Номер', 'code'),
(2, 'Дата', 'pdate'),
(3, 'Контрагент', 'supplier'),
(4, 'Менеджер', 'manager'),
(5, 'Статус', 'status'),
(6, 'Утверждение', 'confirmed');

INSERT INTO `app_contract_view` (`id`, `col_id`, `user_id`, `ord`) VALUES
(1, 1, 0, 10),
(2, 2, 0, 20),
(3, 3, 0, 30),
(4, 4, 0, 40),
(5, 5, 0, 50),
(6, 6, 0, 60),
(7, 7, 0, 70);

INSERT INTO `object_group` (`id`, `name`, `description`, `ord`) VALUES 
('86', 'Раздел "Заявки на договора"', 'Раздел "Заявки на договора"', '0');

INSERT INTO `object` (`id`, `group_id`, `name`, `description`, `ord`) VALUES 
('1150', '2', 'Заявки на договора', 'Раздел "Заявки на договора"', '193'), 
('1151', '86', 'Создание заявки на договор', 'Создание заявки на договор', '0'), 
('1152', '86', 'Изменение заявки на договор', 'Изменение заявки на договор', '0'), 
('1153', '86', 'Удаление заявки на договор', 'Удаление заявки на договор', '0'), 
('1154', '86', 'Утверждение заявки на договор', 'Утверждение заявки на договор', '0'),
('1155', '86', 'Снятие утверждения заявки на договор', 'Снятие утверждения заявки на договор', '0'),
('1156', '86', 'Аннулирование заявки на договор', 'Аннулирование заявки на договор', '0'),
('1157', '86', 'Восстановление заявки на договор', 'Восстановление заявки на договор', '0');

INSERT `user_rights` (`id`, `user_id`, `right_id`, `object_id`) VALUES 
(0, 2, 2, 1150),
(0, 2, 2, 1151),
(0, 2, 2, 1152),
(0, 2, 2, 1153),
(0, 2, 2, 1154),
(0, 2, 2, 1155),
(0, 2, 2, 1156);

INSERT INTO `left_menu_new` (`id`, `parent_id`, `object_id`, `name`, `description`, `url`, `ord`) VALUES 
(null, '0', '0', 'Заявки на договора', 'Раздел "Заявки на договора"', '#', ''),
(null, '28', '1150', 'Заявки на договора', 'Раздел "Заявки на договора"', 'app_contract.php?from=0', '193');

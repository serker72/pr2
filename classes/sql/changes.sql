
/*добавление полей для смены срока выполнения задачи планировщика */

ALTER TABLE `sched` ADD `new_pdate_beg` DATE NULL DEFAULT NULL AFTER `ptime_end`, ADD `new_ptime_beg` TIME NULL DEFAULT NULL AFTER `new_pdate_beg`, ADD `is_waiting_new_pdate` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `new_ptime_beg`, ADD INDEX (`is_waiting_new_pdate`), ADD INDEX (`new_pdate_beg`), ADD INDEX (`new_ptime_beg`);


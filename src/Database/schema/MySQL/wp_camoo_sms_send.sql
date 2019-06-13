DROP TABLE IF EXISTS `wp_camoo_sms_send`;
CREATE TABLE `wp_camoo_sms_send` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sender` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `recipient` text NOT NULL,
  `response` text NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'sent',
  `reference` varchar(75) NOT NULL DEFAULT '',
  `message_id` varchar(100) NOT NULL DEFAULT '',
`status_time` datetime NOT NULL DEFAULT '2019-01-01 00:00:00',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

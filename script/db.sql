
CREATE TABLE IF NOT EXISTS `c_article` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `wp_id` int(11) NOT NULL default 0,
  `wp_status` int(11) NOT NULL default 1 COMMENT '文章状态:1.已发布；2.在回收站',
  `source` int(11) NOT NULL default 1 COMMENT '文章来源：1.xplusplus;2.wordpress',
  `add_timestamp` timestamp NOT NULL DEFAULT 0,
  `mod_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `c_article_r` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `wp_id` int(11) NOT NULL default 0,
  `wp_status` int(11) NOT NULL default 1 COMMENT '文章状态:1.已发布；2.在回收站',
  `source` int(11) NOT NULL default 1 COMMENT '文章来源：1.xplusplus;2.wordpress',
  `add_timestamp` timestamp NOT NULL DEFAULT 0,
  `mod_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `c_id` (
  `name` varchar(255) DEFAULT NULL,
  `id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `c_id`
--

INSERT INTO `c_id` (`name`, `id`) VALUES
('user_id', 19),
('article_id', 34),
('notify_id', 8);

-- --------------------------------------------------------

--
-- 表的结构 `c_notify`
--

CREATE TABLE IF NOT EXISTS `c_notify` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_id_ref` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `notify_type` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `add_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `user_id_ref` (`user_id_ref`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `c_tags`
--

CREATE TABLE IF NOT EXISTS `c_tags` (
  `article_id` int(11) DEFAULT NULL,
  `tag` varchar(255) NOT NULL,
  `add_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `c_tags_r` (
  `article_id` int(11) DEFAULT NULL,
  `tag` varchar(255) NOT NULL,
  `add_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 表的结构 `c_user`
--

CREATE TABLE IF NOT EXISTS `c_user` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `second_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_status` int(11) NOT NULL DEFAULT '1' COMMENT '邮箱状态：1:注册未验证；2.修改未验证；10.已验证',
  `password` varchar(255) NOT NULL,
  `auth_id` varchar(255) NOT NULL DEFAULT '',
  `auth_token` varchar(255) NOT NULL DEFAULT '',
  `auth_expire` bigint(20) NOT NULL,
  `wp_switch` int(11) NOT NULL default '1' COMMENT 'wp状态：1：未开启；2.正常开启',
  `wp_code` varchar(255) NOT NULL default '',
  `wp_home` varchar(255) NOT NULL default '',
  `wp_code_status` int NOT NULL default '1' COMMENT 'wp连接状态:1:未连接，2：已连接，',
  `info` text,
  `feature_bit` int NOT NULL default 0 COMMENT '用来显示是否展示帮助指引的标记',
  `add_timestamp` timestamp NOT NULL DEFAULT 0,
  `mod_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 表的结构 `c_verify`
--

CREATE TABLE IF NOT EXISTS `c_verify` (
  `email` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(255) NOT NULL,
  `expire_time` bigint(20) DEFAULT '0',
  `check_time` bigint(20) DEFAULT '0',
  `add_timestamp` timestamp NOT NULL DEFAULT 0,
  `mod_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 表的结构 `r_follow`
--

CREATE TABLE IF NOT EXISTS `r_follow` (
  `follower` int(11) NOT NULL DEFAULT '0',
  `followee` int(11) NOT NULL DEFAULT '0',
  `add_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`follower`,`followee`),
  KEY `followee` (`followee`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `r_keep`
--

CREATE TABLE IF NOT EXISTS `r_keep` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `article_id` int(11) NOT NULL DEFAULT '0',
  `add_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`article_id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- 表的结构 `c_top`
--

CREATE TABLE IF NOT EXISTS `c_top` (
  `category` varchar(255) NOT NULL DEFAULT '',
  `id` int(11) NOT NULL DEFAULT '0',
  `pri` int(11) NOT NULL DEFAULT '0' COMMENT '优先级，数值越小，优先级约高',
  `mod_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



--
-- 限制导出的表
--

--
-- 限制表 `c_article`
--
ALTER TABLE `c_article`
  ADD CONSTRAINT `c_article_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `c_user` (`id`);

--
-- 限制表 `c_notify`
--
ALTER TABLE `c_notify`
  ADD CONSTRAINT `c_notify_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `c_user` (`id`),
  ADD CONSTRAINT `c_notify_ibfk_2` FOREIGN KEY (`user_id_ref`) REFERENCES `c_user` (`id`);

--
-- 限制表 `c_tags`
--
ALTER TABLE `c_tags`
  ADD CONSTRAINT `c_tags_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `c_article` (`id`);

--
-- 限制表 `r_follow`
--
ALTER TABLE `r_follow`
  ADD CONSTRAINT `r_follow_ibfk_1` FOREIGN KEY (`follower`) REFERENCES `c_user` (`id`),
  ADD CONSTRAINT `r_follow_ibfk_2` FOREIGN KEY (`followee`) REFERENCES `c_user` (`id`);

--
-- 限制表 `r_keep`
--
ALTER TABLE `r_keep`
  ADD CONSTRAINT `r_keep_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `c_user` (`id`),
  ADD CONSTRAINT `r_keep_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `c_article` (`id`);

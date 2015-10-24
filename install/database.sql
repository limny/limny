DROP TABLE IF EXISTS `lmn_adminnav`;
CREATE TABLE `lmn_adminnav` (
  `id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `query` varchar(256) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `lmn_adminnav` (`id`, `title`, `query`) VALUES
(1, 'POST_POST', 'post'),
(2, 'POST_POSTS', 'post/posts'),
(3, 'POST_CATEGORIES', 'post/cats'),
(4, 'PAGE_PAGES', 'page'),
(5, 'GALLERY_GALLERY', 'gallery'),
(6, 'GALLERY_PICTURES', 'gallery/pics'),
(7, 'GALLERY_CATEGORIES', 'gallery/cats'),
(8, 'COMMENT_COMMENT', 'comment'),
(9, 'COMMENT_UNAPPROVED', 'comment/unapproved'),
(10, 'COMMENT_ALL_COMMENTS', 'comment/all');

DROP TABLE IF EXISTS `lmn_apps`;
CREATE TABLE `lmn_apps` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `required_by` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `lmn_apps` (`id`, `name`, `enabled`, `required_by`) VALUES
(1, 'post', 1, 'comment'),
(2, 'page', 1, NULL),
(3, 'gallery', 1, NULL),
(4, 'comment', 1, NULL),
(5, 'ckeditor', 1, NULL);

DROP TABLE IF EXISTS `lmn_codes`;
CREATE TABLE `lmn_codes` (
  `id` int(11) NOT NULL,
  `type` varchar(32) NOT NULL,
  `email` varchar(256) NOT NULL,
  `code` varchar(128) NOT NULL,
  `ip` int(11) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lmn_comments`;
CREATE TABLE `lmn_comments` (
  `id` int(11) NOT NULL,
  `post` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `email` varchar(256) DEFAULT NULL,
  `website` varchar(256) DEFAULT NULL,
  `comment` text NOT NULL,
  `replyto` int(11) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT NULL,
  `ip` int(11) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lmn_config`;
CREATE TABLE `lmn_config` (
  `name` varchar(64) NOT NULL,
  `value` longtext
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `lmn_config` (`name`, `value`) VALUES
('title', 'Limny'),
('motto', 'another site with limny...'),
('description', 'limny content management system'),
('header', 'Limny'),
('footer', 'Copyright &copy; 2015'),
('theme', 'blog'),
('cache_lifetime', '60'),
('default_content', 'app'),
('default_app', 'post'),
('default_query', ''),
('default_text', ''),
('language', 'en'),
('version', '4.0.0'),
('timezone', 'UTC'),
('address', 'http://localhost'),
('calendar', 'gregorian'),
('date_format', 'j M Y'),
('user_registration', '0'),
('url_mode', 'standard'),
('email_confirmation', '0'),
('smtp_host', ''),
('smtp_port', ''),
('smtp_security', 'tls'),
('smtp_auth', '1'),
('smtp_username', ''),
('smtp_password', '');

DROP TABLE IF EXISTS `lmn_gallery`;
CREATE TABLE `lmn_gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `image` varchar(128) DEFAULT NULL,
  `thumbnail` varchar(128) DEFAULT NULL,
  `category` text,
  `time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lmn_gallery_cats`;
CREATE TABLE `lmn_gallery_cats` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `parent` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lmn_menu`;
CREATE TABLE `lmn_menu` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `tooltip` text,
  `address` text NOT NULL,
  `target` varchar(16) DEFAULT NULL,
  `sort` int(11) NOT NULL,
  `enabled` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `lmn_menu` (`id`, `name`, `tooltip`, `address`, `target`, `sort`, `enabled`) VALUES
(1, 'Home', 'Home page', '', '_self', 1, 1);

DROP TABLE IF EXISTS `lmn_pages`;
CREATE TABLE `lmn_pages` (
  `id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `text` longtext NOT NULL,
  `image` varchar(128) DEFAULT NULL,
  `time` int(11) NOT NULL,
  `updated` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lmn_permalinks`;
CREATE TABLE `lmn_permalinks` (
  `id` int(11) NOT NULL,
  `query` varchar(256) NOT NULL,
  `permalink` varchar(256) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `lmn_permalinks` (`id`, `query`, `permalink`) VALUES
(1, 'post/1', 'Hello');

DROP TABLE IF EXISTS `lmn_permissions`;
CREATE TABLE `lmn_permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(256) CHARACTER SET latin1 NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `query` varchar(256) CHARACTER SET latin1 NOT NULL,
  `sub_allowed` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `lmn_permissions` (`id`, `name`, `parent`, `query`, `sub_allowed`) VALUES
(1, 'DASHBOARD', NULL, 'dashboard', NULL),
(2, 'MENU', NULL, 'menu', NULL),
(3, 'ADD', 2, 'menu/add', NULL),
(4, 'DELETE', 2, 'menu/delete', 1),
(5, 'EDIT', 2, 'menu/edit', 1),
(6, 'SORT', 2, 'menu/sort', 1),
(7, 'BLOCKS', NULL, 'blocks', NULL),
(8, 'WIDGETS', NULL, 'widgets', NULL),
(9, 'THEMES', NULL, 'themes', NULL),
(10, 'EDIT', 9, 'themes/edit', 1),
(11, 'CHANGE', 9, 'themes/set', 1),
(12, 'USERS', NULL, 'users', NULL),
(13, 'ADD', 12, 'users/add', NULL),
(14, 'VIEW', 12, 'users/view', 1),
(15, 'EDIT', 12, 'users/edit', 1),
(16, 'DELETE', 12, 'users/delete', 1),
(17, 'SEARCH', 12, 'users/search', NULL),
(18, 'ROLES', NULL, 'roles', NULL),
(19, 'ADD', 18, 'roles/add', NULL),
(20, 'VIEW', 18, 'roles/view', 1),
(21, 'EDIT', 18, 'roles/edit', 1),
(22, 'DELETE', 18, 'roles/delete', 1),
(23, 'APPLICATIONS', NULL, 'apps', NULL),
(24, 'CONFIGURATION', NULL, 'config', 1),
(25, 'POST_POST', NULL, 'post', 0),
(26, 'POST_POSTS', 25, 'post/posts', 0),
(27, 'POST_ADD', 25, 'post/posts/add', 0),
(28, 'POST_VIEW', 25, 'post/posts/view', 1),
(29, 'POST_EDIT', 25, 'post/posts/edit', 1),
(30, 'POST_DELETE', 25, 'post/posts/delete', 1),
(31, 'POST_SEARCH', 25, 'post/posts/search', 0),
(32, 'POST_CATEGORIES', 25, 'post/cats', 0),
(33, 'POST_CATEGORIES_ADD', 25, 'post/cats/add', 0),
(34, 'POST_CATEGORIES_EDIT', 25, 'post/cats/edit', 1),
(35, 'POST_CATEGORIES_DELETE', 25, 'post/cats/delete', 1),
(36, 'PAGE_PAGES', NULL, 'page', 0),
(37, 'PAGE_ADD', 36, 'page/add', 0),
(38, 'PAGE_VIEW', 36, 'page/view', 1),
(39, 'PAGE_EDIT', 36, 'page/edit', 1),
(40, 'PAGE_DELETE', 36, 'page/delete', 1),
(41, 'PAGE_SEARCH', 36, 'page/search', 0),
(42, 'GALLERY_GALLERY', NULL, 'gallery', 0),
(43, 'GALLERY_PICTURES', 42, 'gallery/pics', 0),
(44, 'GALLERY_ADD', 42, 'gallery/pics/add', 0),
(45, 'GALLERY_EDIT', 42, 'gallery/pics/edit', 1),
(46, 'GALLERY_DELETE', 42, 'gallery/pics/delete', 1),
(47, 'GALLERY_CATEGORIES', 42, 'gallery/cats', 0),
(48, 'GALLERY_CATEGORIES_ADD', 42, 'gallery/cats/add', 0),
(49, 'GALLERY_CATEGORIES_EDIT', 42, 'gallery/cats/edit', 1),
(50, 'GALLERY_CATEGORIES_DELETE', 42, 'gallery/cats/delete', 1),
(51, 'COMMENT_COMMENT', NULL, 'comment', 0),
(52, 'COMMENT_UNAPPROVED', 51, 'comment/unapproved', 0),
(53, 'COMMENT_ALL_COMMENTS', 51, 'comment/all', 0),
(54, 'COMMENT_VIEW', 51, 'comment/all/view', 1),
(55, 'COMMENT_EDIT', 51, 'comment/all/edit', 1),
(56, 'COMMENT_DELETE', 51, 'comment/all/delete', 1),
(57, 'COMMENT_SEARCH', 51, 'comment/all/search', 0);

DROP TABLE IF EXISTS `lmn_posts`;
CREATE TABLE `lmn_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `text` longtext NOT NULL,
  `category` text,
  `tags` text,
  `image` varchar(128) DEFAULT NULL,
  `user` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `updated` int(11) DEFAULT NULL,
  `published` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `lmn_posts` (`id`, `title`, `text`, `category`, `tags`, `image`, `user`, `time`, `updated`, `published`) VALUES
(1, 'Hello', '<p>This is an automatic post.<br />\r\nYou can edit or delete this <a href="admin">admin panel</a>.</p>\r\n', NULL, NULL, NULL, 1, UNIX_TIMESTAMP(), NULL, 1);

DROP TABLE IF EXISTS `lmn_posts_cats`;
CREATE TABLE `lmn_posts_cats` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lmn_profiles`;
CREATE TABLE `lmn_profiles` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `nick_name` varchar(256) DEFAULT NULL,
  `first_name` varchar(256) DEFAULT NULL,
  `last_name` varchar(256) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `lmn_profiles` (`id`, `user`, `nick_name`, `first_name`, `last_name`) VALUES
(1, 1, 'Admin', '', '');

DROP TABLE IF EXISTS `lmn_roles`;
CREATE TABLE `lmn_roles` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `permissions` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `lmn_roles` (`id`, `name`, `permissions`) VALUES
(1, 'Administrator', 'all'),
(2, 'Moderator', '1,2,7,8'),
(3, 'User', '');

DROP TABLE IF EXISTS `lmn_users`;
CREATE TABLE `lmn_users` (
  `id` int(11) NOT NULL,
  `username` varchar(256) NOT NULL,
  `password` varchar(128) NOT NULL,
  `email` varchar(256) NOT NULL,
  `roles` text NOT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `ip` int(11) DEFAULT NULL,
  `hash` varchar(128) DEFAULT NULL,
  `last_login` int(11) DEFAULT NULL,
  `last_activity` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `lmn_users` (`id`, `username`, `password`, `email`, `roles`, `enabled`, `ip`, `hash`, `last_login`, `last_activity`) VALUES
(1, 'admin', '$2a$08$M9mkDqTUKUaEqRgshS8ij.CMB2invLC403hJKcA4t7Yxpe0IjtyGO', 'admin@localhost.com', '1', 1, NULL, NULL, NULL, NULL);

DROP TABLE IF EXISTS `lmn_widgets`;
CREATE TABLE `lmn_widgets` (
  `id` int(11) NOT NULL,
  `app` varchar(128) NOT NULL,
  `method` varchar(256) NOT NULL,
  `options` longtext,
  `position` varchar(128) NOT NULL,
  `lifetime` int(11) DEFAULT NULL,
  `roles` text,
  `languages` text,
  `sort` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `lmn_widgets` (`id`, `app`, `method`, `options`, `position`, `lifetime`, `roles`, `languages`, `sort`) VALUES
(1, 'limny', 'user_widget', NULL, 'sidebar', NULL, 'all', 'all', 1),
(2, 'limny', 'content_widget', NULL, 'main', NULL, 'all', 'all', 1);

ALTER TABLE `lmn_adminnav`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_apps`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_codes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_comments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_gallery`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_gallery_cats`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_menu`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_pages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_permalinks`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_permissions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_posts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_posts_cats`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_profiles`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_roles`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_widgets`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lmn_adminnav`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `lmn_apps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `lmn_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lmn_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lmn_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lmn_gallery_cats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lmn_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `lmn_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lmn_permalinks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `lmn_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

ALTER TABLE `lmn_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `lmn_posts_cats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lmn_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `lmn_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `lmn_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `lmn_widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
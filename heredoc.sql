/*
Navicat MySQL Data Transfer

Source Server         : 本地
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : heredoc

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-07-20 18:00:34
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for hbb_cate
-- ----------------------------
DROP TABLE IF EXISTS `hbb_cate`;
CREATE TABLE `hbb_cate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(20) NOT NULL COMMENT '目录名称',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'pid',
  `uid` int(8) unsigned NOT NULL COMMENT '所有者id',
  `itid` int(8) unsigned NOT NULL COMMENT '所属的项目id',
  `sort` tinyint(1) unsigned NOT NULL DEFAULT '10' COMMENT '排序',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `itid` (`itid`,`uid`,`pid`,`sort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='目录表';

-- ----------------------------
-- Records of hbb_cate
-- ----------------------------

-- ----------------------------
-- Table structure for hbb_docs
-- ----------------------------
DROP TABLE IF EXISTS `hbb_docs`;
CREATE TABLE `hbb_docs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(20) NOT NULL COMMENT '文档标题',
  `content` text NOT NULL COMMENT '文档内容',
  `cid` int(10) unsigned NOT NULL COMMENT '所属的目录',
  `uid` int(8) unsigned NOT NULL COMMENT '所有者id',
  `sort` tinyint(1) unsigned NOT NULL DEFAULT '10' COMMENT '排序',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`,`uid`,`sort`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='文档表';

-- ----------------------------
-- Records of hbb_docs
-- ----------------------------

-- ----------------------------
-- Table structure for hbb_items
-- ----------------------------
DROP TABLE IF EXISTS `hbb_items`;
CREATE TABLE `hbb_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(20) NOT NULL COMMENT '项目名称',
  `description` varchar(40) NOT NULL COMMENT '插件描述',
  `url` varchar(10) NOT NULL COMMENT '个性域名',
  `password` varchar(32) NOT NULL COMMENT '访问密码',
  `uid` int(8) unsigned NOT NULL COMMENT '所有者id',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态，0:归档，1：正在编写',
  `sort` tinyint(1) unsigned NOT NULL DEFAULT '10' COMMENT '排序',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='项目表';

-- ----------------------------
-- Records of hbb_items
-- ----------------------------
INSERT INTO `hbb_items` VALUES ('1', '服务平台', '服务平台', 'fwpt', 'fwpt', '1', '1', '10', '1532049053');
INSERT INTO `hbb_items` VALUES ('2', '撒地方', '是否', '', '', '1', '1', '10', '1532049093');
INSERT INTO `hbb_items` VALUES ('3', '阿斯蒂芬', '未', 'dddd', 'wqer ', '1', '1', '10', '1532049135');
INSERT INTO `hbb_items` VALUES ('4', '未', '我去二', 'dddd', 'sdaf ', '1', '1', '10', '1532049145');
INSERT INTO `hbb_items` VALUES ('5', '爱迪生', '的', 'ddd', 'ddddd', '1', '1', '10', '1532049466');

-- ----------------------------
-- Table structure for hbb_template
-- ----------------------------
DROP TABLE IF EXISTS `hbb_template`;
CREATE TABLE `hbb_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(20) NOT NULL COMMENT '模板标题',
  `content` text NOT NULL COMMENT '内容',
  `uid` int(8) unsigned NOT NULL COMMENT '所有者id',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='模板表';

-- ----------------------------
-- Records of hbb_template
-- ----------------------------

-- ----------------------------
-- Table structure for hbb_user
-- ----------------------------
DROP TABLE IF EXISTS `hbb_user`;
CREATE TABLE `hbb_user` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(15) NOT NULL COMMENT '用户名',
  `password` varchar(32) NOT NULL COMMENT '密码',
  `pwd_mix` varchar(10) NOT NULL COMMENT '密码加密',
  `pic` varchar(100) NOT NULL COMMENT '头像',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0：男，1：女，2：保密',
  `tel` varchar(15) NOT NULL COMMENT '电话',
  `email` varchar(25) NOT NULL COMMENT '邮箱',
  `info` varchar(100) NOT NULL COMMENT '个人简介',
  `last_login_ip` varchar(20) NOT NULL COMMENT '上次登录ip',
  `last_login_time` int(11) unsigned NOT NULL COMMENT '上次登录时间',
  `login_time` int(11) unsigned NOT NULL COMMENT '本次登录时间',
  `times` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `addtime` int(11) unsigned NOT NULL,
  `login_ip` varchar(20) NOT NULL COMMENT '登录ip',
  PRIMARY KEY (`id`),
  KEY `tel` (`tel`),
  KEY `email` (`email`),
  KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户表';

-- ----------------------------
-- Records of hbb_user
-- ----------------------------
INSERT INTO `hbb_user` VALUES ('1', 'crasphb', '2a0c140c2b3c9f033f69e5882c94767c', 'fcRKbUn3VG', '', '0', '', '646054215@qq.com', '', '', '0', '1532048983', '1', '1532048845', '0.0.0.0');

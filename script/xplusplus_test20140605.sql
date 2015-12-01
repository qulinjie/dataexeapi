-- phpMyAdmin SQL Dump
-- version 3.4.3.1
-- http://www.phpmyadmin.net
--
-- 主机: localhost:3306
-- 生成日期: 2014 年 06 月 05 日 01:59
-- 服务器版本: 5.6.17
-- PHP 版本: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


--
-- 数据库: `xplusplus`
--

-- --------------------------------------------------------

--
-- 表的结构 `c_article`
--

CREATE TABLE IF NOT EXISTS `c_article` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `add_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mod_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `c_article`
--

INSERT INTO `c_article` (`id`, `title`, `subtitle`, `category_id`, `content`, `user_id`, `add_timestamp`, `mod_timestamp`) VALUES
(1, '面向GC的Java编程', '无', 2, '<p>Java程序员在编码过程中通常不需要考虑内存问题，JVM经过高度优化的GC机制大部分情况下都能够很好地处理堆(Heap)的清理问题。以至于许多Java程序员认为，我只需要关心何时创建对象，而回收对象，就交给GC来做吧！甚至有人说，如果在编程过程中频繁考虑内存问题，是一种退化，这些事情应该交给编译器，交给虚拟机来解决。</p>\r\n<p>这话其实也没有太大问题，的确，大部分场景下关心内存、GC的问题，显得有点“杞人忧天”了，高老爷说过：</p>\r\n<p style="padding-left: 30px;">过早优化是万恶之源。</p>\r\n<p>但另一方面，<strong>什么才是“过早优化”？</strong></p>\r\n<p style="padding-left: 30px;">If we could do things right for the first time, why not?</p>\r\n<p>事实上<strong>JVM的内存模型</strong>( <a href="http://www.cs.umd.edu/~pugh/java/memoryModel/" target="_blank">JMM</a> )理应是Java程序员的基础知识，处理过几次JVM线上内存问题之后就会很明显感受到，很多系统问题，都是内存问题。</p>\r\n<p>对JVM内存结构感兴趣的同学可以看下 <a href="http://blog.hesey.net/2011/04/introduction-to-java-virtual-machine.html" target="_blank">浅析Java虚拟机结构与机制</a> 这篇文章，本文就不再赘述了，本文也并不关注具体的GC算法，相关的文章汗牛充栋，随时可查。</p>\r\n<p>另外，不要指望GC优化的这些技巧，可以对应用性能有成倍的提高，特别是对I/O密集型的应用，或是实际落在YoungGC上的优化，可能效果只是帮你减少那么一点YoungGC的频率。</p>\r\n<p>但我认为，<strong>优秀程序员的价值，不在于其所掌握的几招屠龙之术，而是在细节中见真著</strong>，就像前面说的，<strong>如果我们可以一次把事情做对，并且做好，在允许的范围内尽可能追求卓越，为什么不去做呢？</strong><span id="more-11541"></span></p>\r\n<h4>一、GC分代的基本假设</h4>\r\n<p>大部分GC算法，都将堆内存做分代(Generation)处理，但是为什么要分代呢，又为什么不叫内存分区、分段，而要用面向时间、年龄的“代”来表示不同的内存区域？</p>\r\n<p>GC分代的<strong>基本假设</strong>是：</p>\r\n<p style="padding-left: 30px;"><strong>绝大部分对象的生命周期都非常短暂，存活时间短。</strong></p>\r\n<p>而这些短命的对象，恰恰是GC算法需要首先关注的。所以在大部分的GC中，YoungGC（也称作MinorGC）占了绝大部分，对于负载不高的应用，可能跑了数个月都不会发生FullGC。</p>\r\n<p>基于这个前提，在编码过程中，我们应该<strong>尽可能地缩短对象的生命周期</strong>。在过去，分配对象是一个比较重的操作，所以有些程序员会尽可能地减少new对象的次数，尝试减小堆的分配开销，减少内存碎片。</p>\r\n<p>但是，短命对象的创建在JVM中比我们想象的性能更好，所以，不要吝啬new关键字，大胆地去new吧。</p>\r\n<p>当然前提是不做无谓的创建，对象创建的速率越高，那么GC也会越快被触发。</p>\r\n<p>结论：</p>\r\n<ul>\r\n<li>分配小对象的开销分享小，不要吝啬去创建。</li>\r\n<li>GC最喜欢这种小而短命的对象。</li>\r\n<li>让对象的生命周期尽可能短，例如在方法体内创建，使其能尽快地在YoungGC中被回收，不会晋升(romote)到年老代(Old Generation)。</li>\r\n</ul>\r\n<h4>二、对象分配的优化</h4>\r\n<p>基于大部分对象都是小而短命，并且不存在多线程的数据竞争。这些小对象的分配，会优先在线程私有的<strong> TLAB</strong> 中分配，TLAB中创建的对象，不存在锁甚至是CAS的开销。</p>\r\n<p>TLAB占用的空间在Eden Generation。</p>', 1, '2014-06-05 01:53:11', '2014-06-05 01:53:11'),
(2, 'TCP 的那些事儿', '无', 3, '<p><img class="alignright wp-image-11641" src="http://coolshell.cn//wp-content/uploads/2014/05/xin_2001040422167711230318.jpg" alt="" width="360" height="244" />这篇文章是下篇，所以如果你对TCP不熟悉的话，还请你先看看上篇《<a href="http://coolshell.cn/articles/11564.html" target="_blank">TCP的那些事儿（上）</a>》 上篇中，我们介绍了TCP的协议头、状态机、数据重传中的东西。但是TCP要解决一个很大的事，那就是要在一个网络根据不同的情况来动态调整自己的发包的速度，小则让自己的连接更稳定，大则让整个网络更稳定。在你阅读下篇之前，你需要做好准备，本篇文章有好些算法和策略，可能会引发你的各种思考，让你的大脑分配很多内存和计算资源，所以，不适合在厕所中阅读。</p>\r\n<h4>TCP的RTT算法</h4>\r\n<p>从前面的TCP重传机制我们知道Timeout的设置对于重传非常重要。</p>\r\n<ul>\r\n<li>设长了，重发就慢，丢了老半天才重发，没有效率，性能差；</li>\r\n<li>设短了，会导致可能并没有丢就重发。于是重发的就快，会增加网络拥塞，导致更多的超时，更多的超时导致更多的重发。</li>\r\n</ul>\r\n<p>而且，这个超时时间在不同的网络的情况下，根本没有办法设置一个死的值。只能动态地设置。 为了动态地设置，TCP引入了RTT——Round Trip Time，也就是一个数据包从发出去到回来的时间。这样发送端就大约知道需要多少的时间，从而可以方便地设置Timeout——RTO（Retransmission TimeOut），以让我们的重传机制更高效。 听起来似乎很简单，好像就是在发送端发包时记下t0，然后接收端再把这个ack回来时再记一个t1，于是RTT = t1 &#8211; t0。没那么简单，这只是一个采样，不能代表普遍情况。</p>\r\n<p><span id="more-11609"></span></p>\r\n<h5>经典算法</h5>\r\n<p><a href="http://tools.ietf.org/html/rfc793" target="_blank">RFC793</a> 中定义的经典算法是这样的：</p>\r\n<p style="padding-left: 30px;">1）首先，先采样RTT，记下最近好几次的RTT值。</p>\r\n<p style="padding-left: 30px;">2）然后做平滑计算SRTT（ Smoothed RTT）。公式为：（其中的 α 取值在0.8 到 0.9之间，这个算法英文叫Exponential weighted moving average，中文叫：加权移动平均）</p>\r\n<p style="text-align: center;"><strong>SRTT = ( α * SRTT ) + ((1- α) * RTT)</strong></p>\r\n<p style="padding-left: 30px;">3）开始计算RTO。公式如下：</p>\r\n<p style="text-align: center;"><strong>RTO = min [ UBOUND,  max [ LBOUND,   (β * SRTT) ]  ]</strong></p>\r\n<p>其中：</p>\r\n<ul>\r\n<li>UBOUND是最大的timeout时间，上限值</li>\r\n<li>LBOUND是最小的timeout时间，下限值</li>\r\n<li>β 值一般在1.3到2.0之间。</li>\r\n</ul>\r\n<h5>Karn / Partridge 算法</h5>\r\n<p>但是上面的这个算法在重传的时候会出有一个终极问题——你是用第一次发数据的时间和ack回来的时间做RTT样本值，还是用重传的时间和ACK回来的时间做RTT样本值？</p>\r\n<p>这个问题无论你选那头都是按下葫芦起了瓢。 如下图所示：</p>\r\n<ul>\r\n<li>情况（a）是ack没回来，所以重传。如果你计算第一次发送和ACK的时间，那么，明显算大了。</li>\r\n<li>情况（b）是ack回来慢了，但是导致了重传，但刚重传不一会儿，之前ACK就回来了。如果你是算重传的时间和ACK回来的时间的差，就会算短了。</li>\r\n</ul>\r\n<p><img class="aligncenter wp-image-11605" src="http://coolshell.cn//wp-content/uploads/2014/05/Karn-Partridge-Algorithm.jpg" alt="" width="545" height="243" /></p>\r\n<p>所以1987年的时候，搞了一个叫<a href="http://en.wikipedia.org/wiki/Karn''s_Algorithm" target="_blank">Karn / Partridge Algorithm</a>，这个算法的最大特点是——<strong>忽略重传，不把重传的RTT做采样</strong>（你看，你不需要去解决不存在的问题）。</p>\r\n<p>但是，这样一来，又会引发一个大BUG——<strong>如果在某一时间，网络闪动，突然变慢了，产生了比较大的延时，这个延时导致要重转所有的包（因为之前的RTO很小），于是，因为重转的不算，所以，RTO就不会被更新，这是一个灾难</strong>。 于是Karn算法用了一个取巧的方式——只要一发生重传，就对现有的RTO值翻倍（这就是所谓的 Exponential backoff），很明显，这种死规矩对于一个需要估计比较准确的RTT也不靠谱。</p>\r\n<h5>Jacobson / Karels 算法</h5>', 2, '2014-06-05 01:54:46', '2014-06-05 01:54:46');

-- --------------------------------------------------------

--
-- 表的结构 `c_category`
--

CREATE TABLE IF NOT EXISTS `c_category` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `info` varchar(255) DEFAULT '',
  `parent_id` int(11) DEFAULT NULL,
  `add_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mod_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `c_category`
--

INSERT INTO `c_category` (`id`, `name`, `info`, `parent_id`, `add_timestamp`, `mod_timestamp`) VALUES
(0, '根类', '无', 0, '2014-06-05 01:48:09', '2014-06-05 01:48:09'),
(1, '数据挖掘', '数据挖掘大类', 0, '2014-06-05 01:48:26', '2014-06-05 01:48:26'),
(2, '聚类', '聚类算法', 1, '2014-06-05 01:48:59', '2014-06-05 01:48:59'),
(3, '分类', '分类算法', 1, '2014-06-05 01:49:20', '2014-06-05 01:49:20'),
(4, '机器学习', '机器学习', 0, '2014-06-05 01:50:16', '2014-06-05 01:50:16'),
(5, '支持向量机', '支持向量机', 4, '2014-06-05 01:50:37', '2014-06-05 01:50:37');

-- --------------------------------------------------------

--
-- 表的结构 `c_test`
--

CREATE TABLE IF NOT EXISTS `c_test` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `info` text,
  `join_date` date DEFAULT NULL,
  `add_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mod_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `c_test`
--

INSERT INTO `c_test` (`id`, `name`, `mail`, `password`, `info`, `join_date`, `add_timestamp`, `mod_timestamp`) VALUES
(1, '1234', '佛挡杀佛', ' 发斯蒂芬', ' 冯绍峰', '2014-06-05', '2014-06-05 01:36:58', '2014-06-05 01:38:05');

-- --------------------------------------------------------

--
-- 表的结构 `c_user`
--

CREATE TABLE IF NOT EXISTS `c_user` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `second_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  auth_id varchar(255) NOT NULL default '',
auth_token varchar(255) NOT NULL default '',
  `info` text,
  `add_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mod_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `c_user`
--

INSERT INTO `c_user` (`id`, `first_name`,`second_name`, `email`, `password`, `info`,  `add_timestamp`, `mod_timestamp`) VALUES
(1, '钟','志勇', 'zzysiat@gmail.com', '123456', '我的xplusplus账号', '2014-06-05 01:46:59', '2014-06-05 01:46:59'),
(2, '旺','旺', 'ww@qq.com', '123456', '旺旺的xplusplus',  '2014-06-05 01:47:29', '2014-06-05 01:47:29');

-- --------------------------------------------------------

--
-- 表的结构 `r_follow`
--

CREATE TABLE IF NOT EXISTS `r_follow` (
  `user_id_a` int(11) NOT NULL DEFAULT '0',
  `user_id_b` int(11) NOT NULL DEFAULT '0',
  `add_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id_a`,`user_id_b`),
  KEY `user_id_b` (`user_id_b`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `r_follow`
--

INSERT INTO `r_follow` (`user_id_a`, `user_id_b`, `add_timestamp`) VALUES
(1, 2, '2014-06-05 01:47:47');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `r_keep`
--

INSERT INTO `r_keep` (`user_id`, `article_id`, `add_timestamp`) VALUES
(1, 2, '2014-06-05 01:55:04'),
(2, 1, '2014-06-05 01:55:20');

--
-- 限制导出的表
--

--
-- 限制表 `c_article`
--
ALTER TABLE `c_article`
  ADD CONSTRAINT `c_article_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `c_user` (`id`),
  ADD CONSTRAINT `c_article_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `c_category` (`id`);

--
-- 限制表 `c_category`
--
ALTER TABLE `c_category`
  ADD CONSTRAINT `c_category_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `c_category` (`id`);

--
-- 限制表 `r_follow`
--
ALTER TABLE `r_follow`
  ADD CONSTRAINT `r_follow_ibfk_1` FOREIGN KEY (`user_id_a`) REFERENCES `c_user` (`id`),
  ADD CONSTRAINT `r_follow_ibfk_2` FOREIGN KEY (`user_id_b`) REFERENCES `c_user` (`id`);

--
-- 限制表 `r_keep`
--
ALTER TABLE `r_keep`
  ADD CONSTRAINT `r_keep_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `c_user` (`id`),
  ADD CONSTRAINT `r_keep_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `c_article` (`id`);


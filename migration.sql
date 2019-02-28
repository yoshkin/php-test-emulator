--
-- База данных: `php-test-emulator`
--

-- --------------------------------------------------------

--
-- Структура таблицы `questions`
--

CREATE TABLE IF NOT EXISTS `questions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `question_used_counter` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=101 ;

--
-- Заполняем `questions`
--

drop procedure if exists insert_questions_to_table;

delimiter //
CREATE procedure insert_questions_to_table()
  begin
    declare v_max int unsigned default 100;
    declare v_counter int unsigned default 1;
    truncate table questions;
    start transaction;
    while v_counter <= v_max do
      insert into questions (id, question_used_counter) values ( v_counter, 0 );
      set v_counter=v_counter+1;
    end while;
    commit;
  end //

delimiter ;

call insert_questions_to_table();

-- --------------------------------------------------------

--
-- Структура таблицы `respondents`
--

CREATE TABLE IF NOT EXISTS `persons` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `intellect` varchar(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `results`
--

CREATE TABLE IF NOT EXISTS `results` (
  `test_id` int(10) unsigned NOT NULL DEFAULT '0',
  `question_id` int(10) unsigned NOT NULL DEFAULT '0',
  `person_id` int(10) unsigned NOT NULL DEFAULT '0',
  `result` tinyint(1) unsigned NOT NULL DEFAULT '0',
  KEY `person_id` (`person_id`),
  KEY `result` (`result`),
  KEY `test_id` (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `test`
--

CREATE TABLE IF NOT EXISTS `tests` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `difficulty` varchar(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

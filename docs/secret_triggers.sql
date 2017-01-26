/* biztonsági triggerek
Biztonsági megjegyzések:
- a joomla_db_user -nek ne legyen joga a triggereket irini, listázni, modositani, törölni

user logged in ellenörzés, FIGYELEM a joomla session kezelés database kell, hogy legyen!

- minden felvitel, modositás, törlés csak bejelentkezett usernek engedélyezett
- category delete csak ha nem lezárt (state <> 3) és nincs benne alkategoria vagy question
- question lezárás mindig megengedet, más update, delete csak ha nem folyik a szavazás 
    és nem lezárt (category.state <> 2 and secret = 0) és nincs hozzá options és szavazat
- option insert, update, delete csak ha a questinsában nem folyik a szavazás és nem lezárt (secret = 0)
- vote insert, delete csak ha a question -ban folyik a szavazás (secret = 1)
- vote egy ember egy szavazásban egy opcióra csak egyszer ellenörzés
- category support csak ha suggestion (state = 1)
- question support csak hs suggestion, nem folyik a szavazás (secret = 0 and state = 1)
- option support  csak ha suggestion a question nem lezárt és nem folyik a szavazás (question.secret = 0 and option.state = 1) 
- acredite isert, delete, update csak ha a category nem lezárt (state <> 3)
 */

/* questions */  
DELIMITER $$
DROP TRIGGER IF EXISTS `question_insert_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `question_insert_secret` BEFORE INSERT
    ON `j_pvoks_questions`
    FOR EACH ROW BEGIN
	  SET @C = (SELECT `state` FROM j_pvoks_categories WHERE id=NEW.category_id);
	  IF (@C <> 2) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Category not active';
	  END IF;
	  SET @L = (SELECT COUNT(session_id) AS CC FROM j_session WHERE userid=NEW.created_by AND TIME > (UNIX_TIMESTAMP() - 3600));
	  IF (@L <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'User not logged in';
	  END IF;	
    END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS `question_update_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `question_update_secret` BEFORE UPDATE
    ON `j_pvoks_questions`
    FOR EACH ROW BEGIN
	  SET @C = (SELECT `state` FROM j_pvoks_categories WHERE id=NEW.category_id);
	  IF (@C <> 2) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'New category not active';
	  END IF;
	  SET @C = (SELECT `state` FROM j_pvoks_categories WHERE id=OLD.category_id);
	  IF (@C <> 2) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Old category not active';
	  END IF;
	  IF (OLD.secret = 2) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Question locked';
	  END IF;
	  IF (OLD.secret = 1 AND NEW.secret <> 2) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Voting in Question';
	  END IF;
	  SET @L = (SELECT COUNT(session_id) AS CC FROM j_session WHERE userid=NEW.modfied_by AND TIME > (UNIX_TIMESTAMP() - 3600));
	  IF (@L <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'User not logged in';
	  END IF;	
    END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS `question_delete_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `question_delete_secret` BEFORE DELETE
    ON `j_pvoks_questions`
    FOR EACH ROW BEGIN
	  SET @C = (SELECT `state` FROM j_pvoks_categories WHERE id=NEW.category_id);
	  IF (@C <> 2) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Category not active';
	  END IF;
	  IF (OLD.aecret <> 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Question locked';
	  END IF;
	  SET @W = (SELECT COUNT(ID) AS CC FROM j_pvoks_options WHERE question_id=OLD.id);
	  IF (@W > 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Options exists';
	  END IF;
	  SET @W = (SELECT COUNT(ID) AS CC FROM j_pvoks_voters WHERE question_id=OLD.id);
	  IF (@W > 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Voters exists';
	  END IF;
	  SET @W = (SELECT COUNT(ID) AS CC FROM j_pvoks_votes WHERE question_id=OLD.id);
	  IF (@W > 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Votes exists';
	  END IF;
	  SET @L = (SELECT COUNT(session_id) AS CC FROM j_session WHERE TIME > (UNIX_TIMESTAMP() - 3600));
	  IF (@L <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'User not logged in';
	  END IF;	
	  DELETE FROM j_pvoks_supports WHERE object_id = OLD.id AND (object_type = "question1" OR object_type = "question2");
	  DELETE FROM j_pvoks_options WHERE question_id= OLD.id;
	  DELETE FROM j_pvoks_voters WHERE question_id= OLD.id;
	  DELETE FROM j_pvoks_votes WHERE question_id= OLD.id;
    END$$
DELIMITER ;

/* categories */
DELIMITER $$
DROP TRIGGER IF EXISTS `category_insert_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `category_insert_secret` BEFORE INSERT
    ON `j_pvoks_categories`
    FOR EACH ROW BEGIN
	  IF (NEW.parent_id > 0) THEN
		  SET @C = (SELECT `state` FROM j_pvoks_categories WHERE id=NEW.parent_id);
		  IF (@C <> 2) THEN
			SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Parent category not active';
		  END IF;
	  END IF;
	  SET @L = (SELECT COUNT(session_id) AS CC FROM j_session WHERE userid=NEW.created_by AND TIME > (UNIX_TIMESTAMP() - 3600));
	  IF (@L <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'User not logged in';
	  END IF;	
    END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS `category_update_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `category_update_secret` BEFORE UPDATE
    ON `j_pvoks_categories`
    FOR EACH ROW BEGIN
	  IF (NEW.parent_id > 0) THEN
		  SET @C = (SELECT `state` FROM j_pvoks_categories WHERE id=NEW.parent_id);
		  IF (@C <> 2) THEN
			SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'New parent category not active';
		  END IF;
	  END IF;
	  IF (OLD.parent_id > 0) THEN
	    SET @C = (SELECT `state` FROM j_pvoks_categories WHERE id=OLD.parent_id);
	    IF (@C <> 2) THEN
		  SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Old parent category not active';
	    END IF;
	  END IF;	
	  IF (OLD.state = 3) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'category locked';
	  END IF;
	  SET @L = (SELECT COUNT(session_id) AS CC FROM j_session WHERE userid=NEW.modified_by AND TIME > (UNIX_TIMESTAMP() - 3600));
	  IF (@L <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'User not logged in';
	  END IF;	
    END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS `category_delete_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `category_delete_secret` BEFORE DELETE
    ON `j_pvoks_catgories`
    FOR EACH ROW BEGIN
	  IF (OLD.parent_id > 0) THEN
	    SET @C = (SELECT `state` FROM j_pvoks_categories WHERE id=OLD.parent_id);
	    IF (@C <> 2) THEN
		  SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Parent category not active';
	    END IF;
	  END IF;	
	  IF (OLD.state = 3) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Category locked';
	  END IF;
	  SET @W = (SELECT COUNT(ID) AS CC FROM j_pvoks_categories WHERE parent_id=OLD.id);
	  IF (@W > 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'sucategories exists';
	  END IF;
	  SET @W = (SELECT COUNT(ID) AS CC FROM j_pvoks_questions WHERE question_id=OLD.id);
	  IF (@W > 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Question exists';
	  END IF;
	  SET @L = (SELECT COUNT(session_id) AS CC FROM j_session WHERE TIME > (UNIX_TIMESTAMP() - 3600));
	  IF (@L <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'User not logged in';
	  END IF;	
	  DELETE FROM j_pvoks_categories WHERE parent_id = OLD.id;
	  DELETE FROM j_pvoks_supports WHERE object_id = OLD.id AND object_type = "category";
	  DELETE FROM j_pvoks_questions WHERE category_id= OLD.id;
	  DELETE FROM j_pvoks_members WHERE category_id= OLD.id;
	  DELETE FROM j_pvoks_acrediteds WHERE category_id= OLD.id;
    END$$
DELIMITER ;


/* options */
DELIMITER $$
DROP TRIGGER IF EXISTS `option_insert_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `option_insert_secret` BEFORE INSERT
    ON `j_pvoks_options`
    FOR EACH ROW BEGIN
	  SET @Q = (SELECT `secret` FROM j_pvoks_questions WHERE id=NEW.question_id);
	  IF (@Q <> 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Question is locked';
	  END IF;
	  SET @L = (SELECT COUNT(session_id) AS CC FROM j_session WHERE userid=NEW.created_by AND TIME > (UNIX_TIMESTAMP() - 3600));
	  IF (@L <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'User not logged in';
	  END IF;	
    END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS `option_update_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `option_update_secret` BEFORE UPDATE
    ON `j_pvoks_options`
    FOR EACH ROW BEGIN
	  IF (NEW.question_id <> OLD.question_id) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Question can not change';
	  END IF;
	  SET @Q = (SELECT `secret` FROM j_pvoks_questions WHERE id=NEW.question_id);
	  IF (@Q <> 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Question is locked';
	  END IF;
	  SET @L = (SELECT COUNT(session_id) AS CC FROM j_session WHERE userid=NEW.created_by AND TIME > (UNIX_TIMESTAMP() - 3600));
	  IF (@L <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'User not logged in';
	  END IF;	
    END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS `option_delete_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `option_delete_secret` BEFORE DELETE
    ON `j_pvoks_options`
    FOR EACH ROW BEGIN
	  SET @Q = (SELECT `secret` FROM j_pvoks_questions WHERE id=NEW.question_id);
	  IF (@Q <> 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'Question is locked';
	  END IF;
	  SET @V = (SELECT COUNT(id) AS CC FROM j_pvoks_votes WHERE option_id = OLD.id);
	  IF (@V <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'option is used';
	  END IF;	
	  SET @L = (SELECT COUNT(session_id) AS CC FROM j_session WHERE TIME > (UNIX_TIMESTAMP() - 3600));
	  IF (@L <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'User not logged in';
	  END IF;	
	  DELETE FROM j_pvoks_votes WHERE option_id = OLD.id;
	  DELETE FROM j_pvoks_supports WHERE object_id = OLD.id AND object_type = "option";
    END$$
DELIMITER ;


/* votes */
DELIMITER $$
DROP TRIGGER IF EXISTS `vote_insert_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `vote_insert_secret` BEFORE INSERT
    ON `j_pvoks_votes`
    FOR EACH ROW BEGIN
	  SET @Q = (SELECT `secret` FROM j_pvoks_questions WHERE id=NEW.question_id);
	  IF (@Q <> 1) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'can not vote this question now';
	  END IF;
	  SET @L = (SELECT COUNT(session_id) AS CC FROM j_session WHERE TIME > (UNIX_TIMESTAMP() - 3600));
	  IF (@L <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'User not logged in';
	  END IF;	
    END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS `vote_update_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `vote_update_secret` BEFORE UPDATE
    ON `j_pvoks_options`
    FOR EACH ROW BEGIN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'can not vote update';
    END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS `vote_delete_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `vote_delete_secret` BEFORE DELETE
    ON `j_pvoks_options`
    FOR EACH ROW BEGIN
	  SET @Q = (SELECT `secret` FROM j_pvoks_questions WHERE id=NEW.question_id);
	  IF (@Q <> 1) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'can not delete vote in this question now';
	  END IF;
	  SET @L = (SELECT COUNT(session_id) AS CC FROM j_session WHERE TIME > (UNIX_TIMESTAMP() - 3600));
	  IF (@L <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'User not logged in';
	  END IF;	
    END$$
DELIMITER ;


/* supports */
DELIMITER $$
DROP TRIGGER IF EXISTS `support_insert_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `support_insert_secret` BEFORE INSERT
    ON `j_pvoks_supports`
    FOR EACH ROW BEGIN
	  if (NEW.object_type = "question1") THEN
		  SET @Q = (SELECT `state` FROM j_pvoks_questions WHERE id=NEW.object_id);
		  IF (@Q <> 1) THEN
			SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'question not a suggestion';
		  END IF;
	  END IF;
	  if (NEW.object_type = "question2") THEN
		  SET @Q = (SELECT `secret` FROM j_pvoks_questions WHERE id=NEW.object_id);
		  IF (@Q <> 0) THEN
			SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'question not in disqusion';
		  END IF;
	  END IF;
	  if (NEW.object_type = "option") THEN
		  SET @W = (SELECT `state` FROM j_pvoks_options WHERE id=NEW.object_id);
		  IF (@W <> 1) THEN
			SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'option not a suggestion';
		  END IF;
	  END IF;
	  if (NEW.object_type = "category") THEN
		  SET @W = (SELECT `state` FROM j_pvoks_catgories WHERE id=NEW.object_id);
		  IF (@W <> 1) THEN
			SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'catgory not a suggestion';
		  END IF;
	  END IF;
	  SET @W = (SELECT COUNT)id) FROM j_pvoks_suggestions WHERE object_type=NEW.object_type AND object_id = NEW.object_id AND user_id = NEW.user_id);
	  IF (@W > 0) THEN
			SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'duplicate support';
	  END IF;
	  SET @L = (SELECT COUNT(session_id) AS CC FROM j_session WHERE userid=NEW.user_id AND TIME > (UNIX_TIMESTAMP() - 3600));
	  IF (@L <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'User not logged in';
	  END IF;	
    END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS `support_update_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `support_update_secret` BEFORE UPDATE
    ON `j_pvoks_supports`
    FOR EACH ROW BEGIN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'can not support update';
    END$$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS `support_delete_secret`$$
CREATE
    DEFINER = CURRENT_USER 
    TRIGGER `support_delete_secret` BEFORE DELETE
    ON `j_pvoks_supports`
    FOR EACH ROW BEGIN
	  if (OLD.object_type = "question1") THEN
		  SET @Q = (SELECT `state` FROM j_pvoks_questions WHERE id=OLD.object_id);
		  IF (@Q <> 1) THEN
			SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'question not a suggestion';
		  END IF;
	  END IF;
	  if (OLD.object_type = "question2") THEN
		  SET @Q = (SELECT `secret` FROM j_pvoks_questions WHERE id=OLD.object_id);
		  IF (@Q <> 0) THEN
			SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'question not in disqusion';
		  END IF;
	  END IF;
	  if (OLD.object_type = "option") THEN
		  SET @W = (SELECT `state` FROM j_pvoks_options WHERE id=OLD.object_id);
		  IF (@W <> 1) THEN
			SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'option not a suggestion';
		  END IF;
	  END IF;
	  if (OLD.object_type = "category") THEN
		  SET @W = (SELECT `state` FROM j_pvoks_catgories WHERE id=OLD.object_id);
		  IF (@W <> 1) THEN
			SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'catgory not a suggestion';
		  END IF;
	  END IF;
	  SET @L = (SELECT COUNT(session_id) AS CC FROM j_session WHERE TIME > (UNIX_TIMESTAMP() - 3600));
	  IF (@L <= 0) THEN
		SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'User not logged in';
	  END IF;	
    END$$
DELIMITER ;





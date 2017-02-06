SET NAMES;

DELIMITER $$

DROP TRIGGER  IF EXISTS `szavazat_add` $$

CREATE DEFINER = 'root'@'localhost' TRIGGER `szavazat_add` BEFORE INSERT ON `j_szavazatok` FOR EACH ROW BEGIN
      SELECT COUNT(user_id) FROM j_usertoken WHERE user_id = SHA1(new.user_id) INTO @W;
      IF (@W <= 0) THEN
        SIGNAL SQLSTATE '45000'  SET MESSAGE_TEXT = 'wrong usertoken in insert voks';
      END IF; 
      select count(id) from j_szavazatok where szavazas_id=NEW.szavazas_id and user_id = new.user_id and secret <> sha1(new.secret) into @W;
      if (@W > 0) THEN	
	 SIGNAL SQLSTATE '45000'
          SET MESSAGE_TEXT = 'Wrong secret in insert voks';
      end if;
      SET NEW.secret = SHA1(NEW.secret);
    END */$$
DELIMITER ;


DELIMITER $$

DROP TRIGGER IF EXISTS  `szavazat_delete` $$

CREATE DEFINER = 'root'@'localhost' TRIGGER `szavazat_delete` BEFORE DELETE ON `j_szavazatok` FOR EACH ROW BEGIN
       if (OLD.szavazas_id <> 0) THEN
	  SIGNAL SQLSTATE '45000'
          SET MESSAGE_TEXT = 'Cannot delete voks';       
       end if;
    END */$$


DELIMITER ;


DELIMITER $$

DROP TRIGGER IF EXISTS  `szavazatok_update` $$

CREATE  DEFINER = 'root'@'localhost'  TRIGGER `szavazatok_update` BEFORE UPDATE ON `j_szavazatok` FOR EACH ROW BEGIN
      IF (NEW.szavazas_id = 0 AND old.secret = sha1(new.secret)) THEN
         SET NEW.secret = sha1(new.secret);
      ELSE 
	SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot edit voks';
      end IF;
    END */$$


DELIMITER ;
;

<?php

/**
 * main translate class
 */

class translate
{
	/* setup the initial translation constants */
	static function setUpTranslationConstants()
	{
		if(!defined("SITE_CONFIG_SITE_LANGUAGE"))
		{
			define("SITE_CONFIG_SITE_LANGUAGE", "English (en)");
		}
		
		$db = Database::getDatabase();
		$languageId = $db->getValue("SELECT id FROM language WHERE languageName = ".$db->quote(SITE_CONFIG_SITE_LANGUAGE));
		if(!(int)$languageId)
		{
			return false;
		}
		
		translate::updateAllLanguageContent($languageId);
		
		/* load in the content */
		$rows = $db->getRows("SELECT language_key.languageKey, language_content.content FROM language_content LEFT JOIN language_key ON language_content.languageKeyId = language_key.id WHERE language_content.languageId = ".(int)$languageId);
		if(COUNT($rows))
		{
			foreach($rows AS $row)
			{
				$constantName = "LANGUAGE_".strtoupper($row['languageKey']);
				define($constantName, $row['content']);
			}
		}
	}
	
	/* translation function for JS */
	static function generateJSLanguageCode()
	{
		if(!defined("SITE_CONFIG_SITE_LANGUAGE"))
		{
			define("SITE_CONFIG_SITE_LANGUAGE", "English (en)");
		}
		
		$db = Database::getDatabase();
		$languageId = $db->getValue("SELECT id FROM language WHERE languageName = ".$db->quote(SITE_CONFIG_SITE_LANGUAGE));
		if(!(int)$languageId)
		{
			return false;
		}
		
		/* setup js */
		$js = array();
		$js[] = "/* translation function */";
		$js[] = "function t(key){ ";
		$js[] = "l = {";
		
		/* load in the content */
		$rows = $db->getRows("SELECT language_key.languageKey, language_content.content FROM language_content LEFT JOIN language_key ON language_content.languageKeyId = language_key.id WHERE language_content.languageId = ".(int)$languageId);
		if(COUNT($rows))
		{
			$ljs = array();
			foreach($rows AS $row)
			{
				$ljs[] = "\"".addslashes($row['languageKey'])."\":\"".addslashes(str_replace(array("\r", "\n"), "", self::getTranslation($row['languageKey'])))."\"";
			}
			$js[] = implode(", ", $ljs);
		}
		$js[] = "};";
		
		$js[] = "return l[key.toLowerCase()];";
		$js[] = "}";
		return implode("\n", $js);
	}
	
	static function updateAllLanguageContent($languageId)
	{
		$db = Database::getDatabase();
		/* make sure we have all content records populated */
		$getMissingRows = $db->getRows("SELECT id, languageKey, defaultContent FROM language_key WHERE id NOT IN (SELECT languageKeyId FROM language_content WHERE languageId = ".(int)$languageId.")");
		if(COUNT($getMissingRows))
		{
			foreach($getMissingRows AS $getMissingRow)
			{
				$dbInsert = new DBObject("language_content", array("languageKeyId", "languageId", "content"));
				$dbInsert->languageKeyId 	= $getMissingRow['id'];
				$dbInsert->languageId 		= (int)$languageId;
				$dbInsert->content 			= $getMissingRow['defaultContent'];
				$dbInsert->insert();
			}
		}
	}
	
	static function getTranslation($key, $defaultContent = '')
	{
		/* are we in language debug mode */
		if(SITE_CONFIG_LANGUAGE_SHOW_KEY == "key")
		{
			return strlen($defaultContent)?$defaultContent:$key;
		}
		
		/* return the language translation if we can find it */
		$constantName = "LANGUAGE_".strtoupper($key);
		if(!defined($constantName))
		{
                    if(strlen($defaultContent))
                    {
                        $db = Database::getDatabase();
                        $languageId = $db->getValue("SELECT id FROM language WHERE languageName = ".$db->quote(SITE_CONFIG_SITE_LANGUAGE));
                        if(!(int)$languageId)
                        {
                                return false;
                        }

                        // insert default key value
                        $dbInsert = new DBObject("language_key", array("languageKey", "defaultContent", "isAdminArea"));
                        $dbInsert->languageKey          = $key;
                        $dbInsert->defaultContent       = $defaultContent;
                        $dbInsert->isAdminArea 		= 0;
                        $dbInsert->insert();
						
						// set constant
						define("LANGUAGE_".strtoupper($key), $defaultContent);

                        return $defaultContent;
                    }
                    return "<font style='color:red;'>SITE ERROR: MISSING TRANSLATION *** <strong>".$key."</strong> ***</font>";
		}
		return constant($constantName);
	}
}
?>
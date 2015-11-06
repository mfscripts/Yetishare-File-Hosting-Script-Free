<?php

class fileFolder
{

    static function getFoldersByUser($userId)
    {
        $db   = Database::getDatabase(true);
        $rows = $db->getRows('SELECT * FROM file_folder WHERE userId = ' . $db->quote($userId) . ' ORDER BY folderName ASC');

        return $rows;
    }

    static function loadById($id)
    {
        $db  = Database::getDatabase(true);
        $row = $db->getRow('SELECT * FROM file_folder WHERE id = ' . (int) $id);
        if (!is_array($row))
        {
            return false;
        }

        $folderObj = new fileFolder();
        foreach ($row AS $k => $v)
        {
            $folderObj->$k = $v;
        }

        return $folderObj;
    }

    /**
     * Remove by user
     */
    public function removeByUser()
    {
        // update db
        $db = Database::getDatabase(true);
        $db->query('UPDATE file SET folderId = 0 WHERE folderId = :id', array('id' => $this->id));
        $db->query('DELETE FROM file_folder WHERE id = :id', array('id' => $this->id));
    }

    static function loadAllByAccount($accountId)
    {
        $db = Database::getDatabase(true);
        $rs = $db->getRows('SELECT * FROM file_folder WHERE userId = ' . $db->quote($accountId) . ' ORDER BY folderName');
        if (!is_array($rs))
        {
            return false;
        }

        return $rs;
    }

}

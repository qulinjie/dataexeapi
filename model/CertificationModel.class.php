<?php

class CertificationModel extends Model
{
    public function tableName()
    {
        return 'c_certification';
    }

    /**
     * @param $id
     * @param $user_id
     * @return bool
     */
    public function add($id, $user_id)
    {
        return $this->insert(array(
            'id'      => $id,
            'user_id' => $user_id
        ));
    }

    /**
     * @param $realName
     * @param $fileName
     * @param $filePath
     * @param $id
     * @return bool
     */
    public function updatePersonalAuth($realName, $fileName, $filePath, $id)
    {
        return $this->update(array(
            'real_name'            => $realName,
            'certificate_filename' => $fileName,
            'certificate_filepath' => $filePath
        ), array('id' => $id));
    }

    /**
     * @param $legalPerson
     * @param $companyName
     * @param $license
     * @param $fileName
     * @param $filePath
     * @param $id
     * @return bool | int
     */
    public function updateCompanyAuth($legalPerson, $companyName, $license, $fileName, $filePath, $id)
    {
        return $this->update(array(
            'legal_name'                => $legalPerson,
            'company_name'              => $companyName,
            'business_license'          => $license,
            'business_license_filename' => $fileName,
            'business_license_filepath' => $filePath
        ), array('id' => $id));
    }

    /**
     * @param int $user_id
     * @return array
     */
    public function get($user_id = -1)
    {
        return $this->where('user_id=?',$user_id)->from()->select();
    }
}
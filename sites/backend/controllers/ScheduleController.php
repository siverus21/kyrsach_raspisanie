<?php

namespace App\Controllers;

use App\Models\Schedule;

class ScheduleController
{
    private $model;

    public function __construct()
    {
        $this->model = new Schedule();
    }

    public function GetAllProgram()
    {
        return $this->model->GetAllProgram();
    }

    public function GetAllRoom()
    {
        return $this->model->GetAllRoom();
    }

    public function GetAllLector()
    {
        return $this->model->GetAllLector();
    }

    public function GetAllDiscipline()
    {
        return $this->model->GetAllDiscipline();
    }

    public function UploadInfoDB($file, $nameTable)
    {
        return $this->model->UploadInfoDB($file, $nameTable);
    }

    public function GetAllTablesName()
    {
        return $this->model->GetAllTablesName();
    }

    public function GetTablesFromWriteData()
    {
        return $this->model->GetTablesFromWriteData();
    }
}
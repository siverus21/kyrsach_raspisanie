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
        return $this->model->GetAllInfoTable("program");
    }

    public function GetAllRoom()
    {
        return $this->model->GetAllInfoTable("room");
    }

    public function GetAllLector()
    {
        return $this->model->GetAllInfoTable("lector");
    }

    public function GetAllDiscipline()
    {
        return $this->model->GetAllInfoTable("discipline");
    }

    public function GetAllTrainingFormat()
    {
        return $this->model->GetAllInfoTable("training_format");
    }

    public function GetAllDirection()
    {
        return $this->model->GetAllInfoTable("direction");
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
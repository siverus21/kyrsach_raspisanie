<?php
require_once '/var/www/backend/models/Schedule.php';

class ScheduleController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new Schedule($db);
    }

    // Отображение списка расписаний
    public function listSchedules()
    {
        $schedules = $this->model->getAllSchedules();
        include '../views/schedule/list.php';
    }

    // Отображение формы для создания расписания
    public function createSchedule()
    {
        include '../views/schedule/create.php';
    }

    // Сохранение нового расписания
    public function storeSchedule()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $date = $_POST['date'];
            $time = $_POST['time'];
            $this->model->createSchedule($title, $description, $date, $time);
            header('Location: /');
            exit();
        }
    }

    // Просмотр конкретного расписания
    public function viewSchedule($id)
    {
        $schedule = $this->model->getScheduleById($id);
        include '../views/schedule/view.php';
    }
}

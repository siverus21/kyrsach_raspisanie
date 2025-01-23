<?php
class Schedule
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Получить все расписания
    public function getAllSchedules()
    {
        $result = pg_query($this->db, "SELECT * FROM schedules ORDER BY date, time");
        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error($this->db));
        }
        return pg_fetch_all($result) ?: []; // Возвращает массив данных или пустой массив
    }

    // Получить расписание по ID
    public function getScheduleById($id)
    {
        $query = "SELECT * FROM schedules WHERE id = $1";
        $result = pg_query_params($this->db, $query, [$id]);
        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error($this->db));
        }
        return pg_fetch_assoc($result); // Возвращает одну строку
    }

    // Создать расписание
    public function createSchedule($title, $description, $date, $time)
    {
        $query = "INSERT INTO schedules (title, description, date, time) VALUES ($1, $2, $3, $4)";
        $result = pg_query_params($this->db, $query, [$title, $description, $date, $time]);
        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error($this->db));
        }
        return true;
    }
}

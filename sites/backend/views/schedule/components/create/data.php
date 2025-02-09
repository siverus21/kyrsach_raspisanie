<?

use App\Controllers\ScheduleController;
use App\Controllers\ShowComponents;


class ViewSchedule
{
    private $controller;

    public function __construct()
    {
        $this->controller = new ScheduleController();
    }

    public function ResultData()
    {
        $arResult = array(
            "ALL_ROOMS" => $this->controller->GetAllRoom(),
            "ALL_LECTOR" => $this->FIOLectors(),
            "ALL_PROGRAMS" => $this->controller->GetAllProgram(),
            "ALL_DISCIPLINE" => $this->controller->GetAllDiscipline(),
            "ALL_TRAINING_FORMAT" => $this->controller->GetAllTrainingFormat(),
        );

        $arResult["SELECT_BLOCK"] = $this->GetSelectInfo($arResult["ALL_TRAINING_FORMAT"], $arResult["ALL_PROGRAMS"]);

        return $arResult;
    }

    public function FIOLectors()
    {
        $res = $this->controller->GetAllLector();
        foreach ($res as $key => $item)
            $res[$key]["FIO"] = $item["family"] . " " . $item["name"] . " " . $item["patronymic"];
        return $res;
    }

    public function GetSelectInfo($format, $programs)
    {
        $trainingFormat = array(
            "title" => "Формат обучения",
            "name_for_id" => "id_training_format",
            "items" => $format
        );

        $courseNumber = array(
            "title" => "Курс",
            "name_for_id" => "course_number",
            "items" => array(
                0 => array(
                    "id" => 1,
                    "name" => 1,
                ),
                1 => array(
                    "id" => 2,
                    "name" => 2,
                ),
                2 => array(
                    "id" => 3,
                    "name" => 3,
                ),
                3 => array(
                    "id" => 4,
                    "name" => 4,
                ),
            )
        );

        $program = array(
            "title" => "Кафедра",
            "name_for_id" => "id_program",
            "items" => $programs,
        );

        $direction = array(
            "title" => "Уровень образования",
            "name_for_id" => "id_direction",
            "items" => $this->controller->GetAllDirection(),
        );

        return array(
            "DIRECTION" => $direction,
            "TRAINING_FORMAT" => $trainingFormat,
            "PROGRAM" => $program,
            "COURSE_NUMBER" => $courseNumber,
        );
    }
}

include 'template.php';

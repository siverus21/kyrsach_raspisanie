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
        return array(
            "ALL_ROOMS" => $this->controller->GetAllRoom(),
            "ALL_LECTOR" => $this->FIOLectors(),
            "ALL_PROGRAMS" => $this->controller->GetAllProgram(),
            "ALL_DISCIPLINE" => $this->controller->GetAllDiscipline(),
        );
    }

    public function FIOLectors()
    {
        $res = $this->controller->GetAllLector();
        foreach ($res as $key => $item)
            $res[$key]["FIO"] = $item["family"] . " " . $item["name"] . " " . $item["patronymic"];
        return $res;
    }
}

include 'template.php';

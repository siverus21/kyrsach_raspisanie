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
            "ALL_LECTOR" => $this->controller->GetAllLector(),
            "ALL_PROGRAMS" => $this->controller->GetAllProgram(),
            "ALL_DISCIPLINE" => $this->controller->GetAllDiscipline(),
        );
    }
}

include 'template.php';

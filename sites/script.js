// script.js

document.getElementById("task-form").addEventListener("submit", function (e) {
  e.preventDefault();

  const taskInput = document.getElementById("task-input");
  const taskTime = document.getElementById("task-time");

  if (taskInput.value && taskTime.value) {
    const taskList = document.querySelector(".task-list");

    const newTask = document.createElement("li");
    newTask.textContent = `${taskInput.value} в ${taskTime.value}`;
    taskList.appendChild(newTask);

    taskInput.value = "";
    taskTime.value = "";
  }
});

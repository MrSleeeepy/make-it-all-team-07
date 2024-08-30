// Adding the cards on page load, sorted
window.addEventListener('DOMContentLoaded', (event) => {
    search_sort_process();
});


// The radio button responsible for changing between the employee and project part of the dashboard
const employee_project_switch = document.getElementsByName('btn_toggle_emp_proj');
employee_project_switch.forEach((button) =>
    button.addEventListener('change', function() {
        // Toggling the taskbar elements that needed to be hidden / unhidden
        const elements = document.querySelectorAll('.toggleVis')
        elements.forEach((element) => {
            element.classList.toggle('hidden');
        })
        // Will trigger the create card function later depending on which button is selected
        search_sort_process();
    })
)


function get_projects_data(){ // call the php file to get projects data
    return fetch('manager_resources/db_queries.php?type=all_projects_data')
    .then(response => response.json())
    .then(data =>{
        return data;

    })
    .catch(error => console.error("Error", error));
}


function get_projects_data_for_teamLeader(teamLeaderID){ // call the php file to get projects data for a specific team leader
    return fetch(`manager_resources/db_queries.php?type=projects_data_for_teamLeader&teamLeaderID=${teamLeaderID}`)
    .then(response => response.json())
    .then(data =>{
        return data;

    })
    .catch(error => console.error("Error", error));
}


function get_employees_data(){ // call the php file to get employees data
    return fetch('manager_resources/db_queries.php?type=all_employees_data')
    .then(response => response.json())
    .then(data =>{
        return data;
    })
    .catch(error => console.error("Error", error));
}


function get_employees_data_for_teamLeader(teamLeaderID){ // call the php file to get employees data for a specific team leader
    return fetch(`manager_resources/db_queries.php?type=employees_data_for_teamLeader&teamLeaderID=${teamLeaderID}`)
    .then(response => response.json())
    .then(data =>{
        return data;
    })
    .catch(error => console.error("Error", error));
}


function get_employees_name_and_id(){ // call the php file to get employees name and id
    return fetch('manager_resources/db_queries.php?type=all_employees_name_and_id')
    .then(response => response.json())
    .then(data =>{
        return data;
    })
    .catch(error => console.error("Error", error));
}


function get_three_upcoming_tasks(projectID){ // call the php file to get 3 tasks name and deadline which has ealiest deadline and is not completed
    return fetch(`manager_resources/db_queries.php?type=tasks_on_cards&projectID=${projectID}`)
    .then(response => response.json())
    .then(data =>{
        return data;
    })
    .catch(error => console.error("Error", error));
}


function search_sort_process(){ // the function used to refresh the data to display 
    const entered_value = searchbar.value.toLowerCase();
    let curren_object = document.querySelector('input[name="btn_toggle_emp_proj"]:checked').value;

    if (role == "teamLeader"){
        document.getElementById("button_create_project").innerHTML = "";
        // When the current switch is on projects
        if (curren_object == 1) {
            let current_sorting_method = projectSort.value;
            
            get_projects_data_for_teamLeader(teamLeaderID).then(projects_data_array => {
                let array_after_search = [];


                // Get the value from the search bar and search through the names
                for (let i = 0; i < projects_data_array.length; i++) {
                    if ((projects_data_array[i].ProjectName).toLowerCase().includes(entered_value)) {
                        array_after_search.push(projects_data_array[i]);
                    }
                }

                // If the length of the array is greater than 1
                if (array_after_search.length > 1){
                    if (current_sorting_method == "project_name") { // if sorting method is project name
                        array_after_search.sort((a, b) => a.ProjectName.localeCompare(b.ProjectName));
                        
                    } else if (current_sorting_method == "employee_count") { // If sorting method is employee count
                        array_after_search.sort((a, b) => a.NumberOfEmployees - b.NumberOfEmployees);

                    } else if (current_sorting_method == "project_tasks_completed_ascending") { // If sorting method is completed tasks in ascending order
                        array_after_search.sort((a, b) => a.NumberOfTasksCompleted - b.NumberOfTasksCompleted);

                    } else if (current_sorting_method == "project_tasks_completed_descending") { // If sorting method is completed tasks in descending order
                        array_after_search.sort((a, b) => b.NumberOfTasksCompleted - a.NumberOfTasksCompleted);
                    }

                    // soritng methods need to have 2 more which are: by creation date and by due date

                }
                // Reset the card section
                document.getElementById("title_dashboard_container").innerHTML = "Projects";
                document.getElementById("card-container").innerHTML = "";

                // create the cards for projects
                create_cards_projects(array_after_search);
            });

        // When the switch is on employees
        } else if (curren_object == 2) {
            let current_sorting_method = empSort.value;

            get_employees_data_for_teamLeader(teamLeaderID).then(employees_data_array => {
                let array_after_search = [];

                // Get the value from the search bar and search through the names
                for (let i = 0; i < employees_data_array.length; i++) {
                    if ((employees_data_array[i].firstName).toLowerCase().includes(entered_value) || (employees_data_array[i].surname).toLowerCase().includes(entered_value)) {
                        array_after_search.push(employees_data_array[i]);
                    }
                }

                // If the length of the array is greater than 1
                if (array_after_search.length > 1){
                    if (current_sorting_method == "emp_fname") { // if sorting method is by employee first name
                        array_after_search.sort((a, b) => a.firstName.localeCompare(b.firstName));
                        
                    } else if (current_sorting_method == "emp_lname") { // if sorting method is by employee last name
                        array_after_search.sort((a, b) => a.surname.localeCompare(b.surname));
                    }
                }

                // Reset the card section
                document.getElementById("title_dashboard_container").innerHTML = "Employees";
                document.getElementById("card-container").innerHTML = "";

                // create card for employees
                create_cards_employees(array_after_search);
            })
        }
    } else {

        // When the current switch is on projects
        if (curren_object == 1) {
            let current_sorting_method = projectSort.value;
            
            get_projects_data().then(projects_data_array => {
                let array_after_search = [];

                // Get the value from the search bar and search through the names
                for (let i = 0; i < projects_data_array.length; i++) {
                    if ((projects_data_array[i].ProjectName).toLowerCase().includes(entered_value)) {
                        array_after_search.push(projects_data_array[i]);
                    }
                }

                // If the length of the array is greater than 1
                if (array_after_search.length > 1){
                    if (current_sorting_method == "project_name") { // if sorting method is project name
                        array_after_search.sort((a, b) => a.ProjectName.localeCompare(b.ProjectName));
                        
                    } else if (current_sorting_method == "employee_count") { // If sorting method is employee count
                        array_after_search.sort((a, b) => a.NumberOfEmployees - b.NumberOfEmployees);

                    } else if (current_sorting_method == "project_tasks_completed_ascending") { // If sorting method is completed tasks in ascending order
                        array_after_search.sort((a, b) => a.NumberOfTasksCompleted - b.NumberOfTasksCompleted);

                    } else if (current_sorting_method == "project_tasks_completed_descending") { // If sorting method is completed tasks in descending order
                        array_after_search.sort((a, b) => b.NumberOfTasksCompleted - a.NumberOfTasksCompleted);
                    }

                    // soritng methods need to have 2 more which are: by creation date and by due date

                }
                // Reset the card section
                document.getElementById("title_dashboard_container").innerHTML = "Projects";
                document.getElementById("card-container").innerHTML = "";

                // create the cards for projects
                create_cards_projects(array_after_search);
            });
        // When the switch is on employees
        } else if (curren_object == 2) {
            let current_sorting_method = empSort.value;

            get_employees_data().then(employees_data_array => {
                let array_after_search = [];

                // Get the value from the search bar and search through the names
                for (let i = 0; i < employees_data_array.length; i++) {
                    if ((employees_data_array[i].firstName).toLowerCase().includes(entered_value) || (employees_data_array[i].surname).toLowerCase().includes(entered_value)) {
                        array_after_search.push(employees_data_array[i]);
                    }
                }

                // If the length of the array is greater than 1
                if (array_after_search.length > 1){
                    if (current_sorting_method == "emp_fname") { // if sorting method is by employee first name
                        array_after_search.sort((a, b) => a.firstName.localeCompare(b.firstName));
                        
                    } else if (current_sorting_method == "emp_lname") { // if sorting method is by employee last name
                        array_after_search.sort((a, b) => a.surname.localeCompare(b.surname));
                    }
                }

                // Reset the card section
                document.getElementById("title_dashboard_container").innerHTML = "Employees";
                document.getElementById("card-container").innerHTML = "";

                // create card for employees
                create_cards_employees(array_after_search);
            })
        }
    }
}

// When the sort by for employees is changed, call the search_sort function
empSort.addEventListener("change", function() {
    search_sort_process();
});


// When the sort by for projects is changed, call the search_sort function
projectSort.addEventListener("change", function() {
    search_sort_process();
});


// Create the cards used to display projects
function create_cards_projects(obj_array) {
    const cardContainer = document.getElementById("card-container");
    // Looping through every project and creating a card
    for (let i = 0; i < obj_array.length; i++) {
        const card = document.createElement("div");
        card.className = "card";
        card.id = obj_array[i].ProjectID;
        
        // call the function to get tasks to display on the project card
        get_three_upcoming_tasks(obj_array[i].ProjectID).then(result_array => {
            card_task_list = [];
            for (let j = 0; j < result_array.length; j++) {
                card_task_list += result_array[j].name + "<br>";
            }

            // Setting the text / layout of the card
            card.innerHTML = '<h5 class="card-header">' + obj_array[i].ProjectName + '</h5><div class="mt-2">Due Tasks: </div><br><div class="overflow-y-auto mt-2" style="height:170px">' + card_task_list + '</div>';
        })

        cardContainer.appendChild(card);

        // Creating a modal that is specific for each card and appears when a card is clicked
        card.addEventListener("click", () => open_project_dashboard(obj_array[i].ProjectID));
    }
}


// Responsible for opening the dashboard of more detailed infomation about a specific project
function open_project_dashboard(project_id) {
    // Clearing the current page contents (beside navbar)
    parentDiv = document.getElementById('data_display_parent');
    parentDiv.innerHTML = ""; 
    document.getElementById("taskbar").style.display = "none";
    
    // Creating the info bar at the top of the project dashboard
    const projTitleDiv = document.createElement('div');
    projTitleDiv.id = 'projTitleDiv';
    parentDiv.appendChild(projTitleDiv)
    // Creating the container for the tiles in the dashboard
    const dashboardContainerDiv = document.createElement('div');
    dashboardContainerDiv.id = 'dashboardContainerDiv';
    parentDiv.appendChild(dashboardContainerDiv);
    const dashboardDiv = document.createElement('div');
    dashboardDiv.id = 'dashboardDiv';
    dashboardContainerDiv.appendChild(dashboardDiv);

    // Gets the projects name
    fetch(`manager_resources/db_queries.php?type=projName&projectID=${project_id}`)
        .then(response => response.json())
        .then(data => {
            // HTML for return button and displaying the projects name
            // Arrow return left icon from bootstrap icons, https://icons.getbootstrap.com/icons/arrow-return-left/
            let topBarHtml = `<div class="row"><div class="col-auto"><button class="btn btn-outline-danger m" id="exitDashboardButton" onclick="exitDashboard()"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-return-left" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M14.5 1.5a.5.5 0 0 1 .5.5v4.8a2.5 2.5 0 0 1-2.5 2.5H2.707l3.347 3.346a.5.5 0 0 1-.708.708l-4.2-4.2a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 8.3H12.5A1.5 1.5 0 0 0 14 6.8V2a.5.5 0 0 1 .5-.5"/>
            </svg></button></div><div class="col"><h2 class="ms-3" id="projectName" title="Project ID: ${project_id}">${data[0].name}</h2></div>`;
            
            // If the role is teamleader, then they can't see the delete project option
            if (role == "teamLeader") {
                topBarHtml += `</div>`;
            } else {
                // Trash icon from bootstrap icons, https://icons.getbootstrap.com/icons/trash/
                topBarHtml += `<div class="col-auto"><div style="float: right; margin-right: 10px"><button class="btn btn-danger" onclick="confirmdeleteProject(${project_id})" style="width:50px; height:50px">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
            </svg></button></div></div></div>`;
            }
            projTitleDiv.innerHTML = topBarHtml;
        });
    

    // Creating the tiles for the project dashboard
    let tileNames = ["projTileSummary", "projTileTasksDue", "projTileEmpTaskBreakdown", "projTileHighPriority", "projTileCreateNewTask"]
    tileNames.forEach(name => {
        let newTile = document.createElement('div');
        newTile.id = name;
        newTile.className = 'tile';
        dashboardDiv.appendChild(newTile);        
    });
    // Creating the larger tile for the task table
    let newTile = document.createElement('div');
    newTile.id = "TaskListTile";
    newTile.className = 'large-tile';
    dashboardDiv.appendChild(newTile);

    // Creating data inputs for users to create new tasks
    const tileNewTask = document.getElementById('projTileCreateNewTask');
    tileNewTask.innerHTML =`<div class="row mb-3">
                            <div class="col">
                            <input type="text" class="form-control" placeholder="Task Name" id="newTaskNameTxt">
                            </div>
                            <div class="col">
                            <div class="dropdown">
                            <input type="hidden" id="newTaskEmpId">
                            <button class="btn btn-secondary dropdown-toggle dropdown-button" type="button" id="dropdownButtonEmployees" onclick="displayDropdownEmployees()">Select Employee</button>
                            <div class="dropdown-menu" id="dropdownMenu">
                                <input type="search" class="form-control" id="dropdownSearchEmployees" placeholder="Search..." autocomplete="off" onkeyup="filterEmployeeList()">                                
                                <div class="dropdown-menu-scroll">
                                <ul id="dropdownMenuEmployees">
                                </ul>
                                </div>
                            </div>
                            </div>
                            </div>
                            </div>
                            <div class="row mb-3">
                            <div class="col">
                            <div class="input-group">
                            <input type="text" class="form-control" pattern="\d*" id="newTaskDuration" placeholder="Duration">
                            <span class="input-group-text">hours</span>
                            </div>
                            </div>
                            <div class="col">
                            <input type="date" id="newTaskDueDate" class="form-control">
                            </div>
                            </div>
                            <div class="mb-3">
                            <textarea class="form-control" placeholder="Description..." id="newTaskDescription" style="height: 75px; max-height: 75px;"></textarea>
                            </div>
                            <div class="row">
                            <div class="col">
                            <div class="mb-3 form-check form-check-reverse">
                            <label class="form-check-label" for="newTaskHighPriority">High Priority</label>
                            <input type="checkbox" class="form-check-input" id="newTaskHighPriority">
                            </div>
                            </div>
                            <div class="col">
                            <button class="btn btn-primary" id="btnCreateNewTask" onclick="validateTaskInputs()">Create Task</button>
                            </div>
                            </div>
                            <div><p id="newTaskErrorMessage" class="errorMsg"></p></div>`;
    
    fillProjProgressionTile(project_id);
    fillProjEmpBreakdownTile(project_id);
    fillTileSummary(project_id);
    fillTileHighestPriorityProj(project_id);
    fillEmpNameDropdown(project_id);
    fillTileTaskTable(project_id);
}


// Confirms a users wants to delete the project with an alert, then does so
async function confirmdeleteProject(projectID) {
    // An alert to confirm
    let confirmDeletion = confirm(`Are you sure you want to delete this project (this can't be undone)`);
    if (confirmDeletion) {
        fetch('manager_resources/db_write.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `type=deleteProject&projectID=${projectID}`
        })
        .then(response => response.json())
        .then(data => {
            exitDashboard();
        })
    }
}


// Deletes a user from the database
async function confirmdeleteUser(userID) {
    // Confirming with the user that they want to delete an employee
    let confirmDeletion = confirm(`Are you sure you want to delete this employee (this can't be undone)`);
    if (confirmDeletion) {
        fetch('manager_resources/db_write.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `type=deleteUser&userID=${userID}`
        })
        .then(response => response.json())
        .then(data => {
            exitDashboard();
        })
    }
}


// Fills the dropdown menu with employee names
async function fillEmpNameDropdown() {
    // Clears the dropdown menu so names don't duplicate
    document.getElementById('dropdownMenuEmployees').innerHTML = "";
    fetch(`manager_resources/db_queries.php?type=emp_list`)
        .then(response => response.json())
        .then(data => {
            data.forEach(employee => {
                // Create the individual selectable item
                const newEmpItem = document.createElement('li');
                newEmpItem.textContent = `${employee.firstName} ${employee.surname}`
                newEmpItem.classList = "dropdownItem"
                newEmpItem.onclick = function () {
                    // When clicked close the menu, clear the search and set the selected employee value
                    document.getElementById('dropdownSearchEmployees').value = "";
                    filterEmployeeList();
                    document.getElementById('dropdownMenu').classList.toggle("show");
                    document.getElementById('dropdownButtonEmployees').textContent = `${employee.firstName} ${employee.surname}`;
                    document.getElementById('newTaskEmpId').value = employee.userID;
                }
                document.getElementById('dropdownMenuEmployees').appendChild(newEmpItem);
            });
        });
}


// Fills the large tile for all task data with a table
async function fillTileTaskTable(project_id) {
    fetch(`manager_resources/db_queries.php?type=proj_all_task_info&projectID=${project_id}`)
        .then(response => response.json())
        .then(data => {
            // Clearing the table for when it is refreshed
            document.getElementById('TaskListTile').innerHTML = "";
            // Creating the table
            const table = document.createElement('table');
            const thead = document.createElement('thead');
            const tbody = document.createElement('tbody');
            tbody.id = "projectTableBody";
            table.className = "taskTable";
            thead.className = "tableHeadStyle";

            // Creating the headers of the table
            const titles = ['Assigned To', 'Duration', 'Due Date', 'High Priority', 'Completed', ''];
            const tr = document.createElement('tr');

            const th = document.createElement('th'); // Having the first row be a search filter for task names
            th.innerHTML = `<input type="search" class="form-control" id="searchTaskNameTableProj" placeholder="Task" autocomplete="off" onkeyup="filterTaskTableProj()"></input>`;
            tr.appendChild(th);

            titles.forEach(title => {
                const th = document.createElement('th');
                th.textContent = title;
                tr.appendChild(th);
            })
            thead.appendChild(tr);

            // Filling the table rows with data
            data.forEach(task => {
                const tr = document.createElement('tr');

                const td1 = document.createElement('td'); // Task name
                td1.textContent = task.name;
                td1.className = "TaskNameCell";
                tr.appendChild(td1);
                const td2 = document.createElement('td'); // Persons name
                td2.textContent = `${task.firstName} ${task.surname}`;
                tr.appendChild(td2);
                const td3 = document.createElement('td'); // Duration
                td3.textContent = task.timeEstimate;
                tr.appendChild(td3);
                const td4 = document.createElement('td'); // Deadline
                td4.textContent = task.deadline;
                tr.appendChild(td4);
                const td6 = document.createElement('td'); // Priority Switch
                td6.innerHTML = `<div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="prioritySwitch${task.taskID}" onclick="toggleTaskPriority(${task.taskID})"></div>`;
                tr.appendChild(td6);
                const td5 = document.createElement('td'); // Completion Switch
                td5.innerHTML = `<input class="form-check-input" type="checkbox" id="checkBoxTaskComplete${task.taskID}" onclick="toggleTaskComplete(${task.taskID})">`;
                tr.appendChild(td5);
                const td7 = document.createElement('td'); // Delete button
                // Trash icon from bootstrap icons, https://icons.getbootstrap.com/icons/trash/
                td7.innerHTML = `<button type="button" class="btn btn-secondary" onclick="deleteTask(${task.taskID})">
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                   <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
                                   <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                                   </svg></button>`;
                                   
                tr.appendChild(td7);

                tbody.appendChild(tr);
            });

            table.appendChild(thead);
            table.appendChild(tbody);
            TaskListTile.appendChild(table);
            
            data.forEach(task => {
                if (task.isCompleted == 1) { // Toggling on the isCompleted switch if it's already completed
                    document.getElementById(`checkBoxTaskComplete${task.taskID}`).checked = true;
                }
                if (task.highPriority == 1) { // Toggling the high priority switch if it's already high
                    document.getElementById(`prioritySwitch${task.taskID}`).checked = true;
                }
            })
        });
}


// Hides certain rows in the table if there is a search filter applied
function filterTaskTableProj(){
    // Getting the text to use as a filter
    const textFilter = document.getElementById("searchTaskNameTableProj").value.toLowerCase();
    // Getting an array of all table rows
    const taskList = Array.from(document.getElementById("projectTableBody").getElementsByTagName("tr"));
    taskList.forEach(task => {
        // Getting the individual rows task name
        const taskName = task.children[0].innerHTML;
        if (taskName.toLowerCase().indexOf(textFilter) > -1) {
            task.style.display = "";
        } else { // Hiding element if doesn't contain text entered
            task.style.display = "none";
        }
    })
}


// Hides certain rows in the task table for employees based on search filter from user
function filterTaskTableEmp(){
    const textFilter = document.getElementById("searchTaskNameTableEmp").value.toLowerCase();
    const taskList = Array.from(document.getElementById("employeeTableBody").getElementsByTagName("tr"));
    taskList.forEach(task => {
        const taskName = task.children[0].innerHTML;
        if (taskName.toLowerCase().indexOf(textFilter) > -1) {
            task.style.display = "";
        } else {
            task.style.display = "none";
        }
    })
}


// Gets the highest priority tasks that need to be completed
async function fillTileHighestPriorityProj(project_id){
    fetch(`manager_resources/db_queries.php?type=high_priority&projectID=${project_id}`)
    .then(response => response.json())
    .then(data => {
        // Clearing on load for when the dashboard is refreshed
        document.getElementById('projTileHighPriority').innerHTML = "";
        const priorityList = document.createElement('ul');
        // If there is no high priority tasks, display a tile to say so
        if (data.length == 0) {
            const priorityListItem = document.createElement('li');
            priorityListItem.innerHTML = "No tasks due";
            priorityListItem.className = "priorityLI";
            priorityList.appendChild(priorityListItem);

        } else { // If there are high priority tasks
            data.forEach(task => {
                const priorityListItem = document.createElement('li');
                let priorityTaskHtml;
                // Shortening the task name if it is too long to display
                if (task.name.length > 25) {
                  const taskName = task.name;
                  priorityTaskHtml = `${taskName.slice(0,26).trim()}...`;
                } else {
                  priorityTaskHtml = `${task.name}`;
                }
                // Adding a high priority badge if it is high priority
                if (task.highPriority == 1) {
                    priorityTaskHtml += `<span class='highPriorityText badge text-bg-primary ms-3'>High priority</span>`;
                }
                priorityTaskHtml += `<br>`;
                // Deciding if the deadline has already passed or is in the future
                const daysUntilDeadline = daysUntil(task.deadline);
                if (daysUntilDeadline > 0) {
                    priorityTaskHtml += `Due: ${daysUntilDeadline} days`;
                } else if (daysUntilDeadline < 0) {
                    priorityTaskHtml += `Due: ${Math.abs(daysUntilDeadline)} days ago`;
                } else {
                    priorityTaskHtml += `Due: today`;
                }
                
                priorityListItem.innerHTML = priorityTaskHtml;
                priorityListItem.className = "priorityLI";
                priorityList.appendChild(priorityListItem);
            })
        }
        projTileHighPriority.innerHTML = `<h3>Highest Priority</h3>`;
        projTileHighPriority.appendChild(priorityList);
    });
}


// Fills the summary tile for projects
async function fillTileSummary(project_id) {
    let proj_sum_tile = document.getElementById("projTileSummary");

    // Waiting for the fetch query to get a response and then converting to json
    const response_summ = await fetch(`manager_resources/db_queries.php?type=proj_summ&projectID=${project_id}`);
    const proj_summ = await response_summ.json();

    const response_leader = await fetch(`manager_resources/db_queries.php?type=proj_leader&projectID=${project_id}`);
    const team_leader = await response_leader.json();

    // Processing the data if needed then displaying within the tile
    let hoursCompletePercent;
    if (proj_summ[0].totalHours == 0) {
        hoursCompletePercent = 0;
    } else {
        hoursCompletePercent = Math.round((proj_summ[0].hoursCompleted / proj_summ[0].totalHours) * 100)
    }

    proj_sum_tile.innerHTML = `<h3>Overview</h3>
    <table class="overviewTable centredElement"><tr><td>Project</td><td>${hoursCompletePercent}% complete</td></tr>
    <tr><td>To Go</td><td>${(proj_summ[0].totalHours - proj_summ[0].hoursCompleted).toFixed(1)} hours</td></tr>
    <tr><td>Completed</td><td>${proj_summ[0].completed} tasks</td></tr>
    <tr><td>Overdue</td><td>${proj_summ[0].overdue} tasks</td></tr>
    <tr><td>Team Leader</td><td>
    <div class="drop-up">
        <button class="btn btn-secondary dropdown-toggle dropdown-button" type="button" id="dropdownButtonSelectTL" onclick="displayDropdownSelectTL()"></button>
        <div class="dropdown-menu dropup-menu" id="dropdownMenuTL">
            <input type="search" class="form-control" id="dropdownSearchTL" placeholder="Search..." autocomplete="off" onkeyup="filterListTL()">                                
            <div class="dropdown-menu-scroll">
            <ul id="dropdownMenuTLList">
            </ul>
            </div>
        </div>
    </div>    
    </td></tr></table>`;

    // Setting the team leader name, if there is one
    if (team_leader[0].firstName && team_leader[0].surname) {
        document.getElementById('dropdownButtonSelectTL').textContent = `${team_leader[0].firstName} ${team_leader[0].surname}`;
    } else {
        document.getElementById('dropdownButtonSelectTL').textContent = "Select Teamleader"
    }

    fillTLDropdown(project_id);
}


// Toggles the dropdown menu
function displayDropdownSelectTL() {
    document.getElementById('dropdownMenuTL').classList.toggle("show");
}


// Hides certain rows depending on the search filter applied by the user for team leader dropdown
function filterListTL() {
    const textFilter = document.getElementById("dropdownSearchTL").value.toLowerCase();
    const employeeList = Array.from(document.getElementById("dropdownMenuTLList").getElementsByTagName("li"));
    employeeList.forEach(employee => {
        if (employee.textContent.toLowerCase().indexOf(textFilter) > -1) {
            employee.style.display = "";
        } else {
            employee.style.display = "none";
        }
    });
}


// Fills the dropdown menu for team leader selection of a project (when on dashboard)
async function fillTLDropdown(projectID){
    fetch(`manager_resources/db_queries.php?type=emp_list`)
    .then(response => response.json())
    .then(data => {
        data.forEach(employee => {
            // Creating the employee name list items
            const newEmpItem = document.createElement('li');
            newEmpItem.textContent = `${employee.firstName} ${employee.surname}`;
            newEmpItem.classList = "dropdownItem";
            // Closing and clearing the dropdown on click, changing the team leader selected in the database
            newEmpItem.onclick = function () {
                document.getElementById('dropdownSearchTL').value = "";
                filterEmployeeList();
                document.getElementById('dropdownMenuTL').classList.toggle("show");
                document.getElementById('dropdownButtonSelectTL').textContent = `${employee.firstName} ${employee.surname}`;
                changeTeamLeader(employee.userID, projectID);
            }
            document.getElementById('dropdownMenuTLList').appendChild(newEmpItem);
        });
    });
}


// Changes the team leader of a project
function changeTeamLeader(userID, projectID){
    fetch('manager_resources/db_write.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `type=changeTeamLeader&userID=${userID}&projectID=${projectID}`
    })
}


// Refreshes the dashboard when theme switch clicked, allows for graph colour change
document.getElementById("darkSwitch").addEventListener("click", function () {
    try {
        const dashboardType = document.getElementById("exitDashboardButton").textContent;
        refreshDashboard();
    } catch (error) {
    }
})


// Returns the colour scheme for graphs, allows changing colours for light/dark modes
function getGraphColourScheme(graphEmpty){
    if (document.body.getAttribute('data-theme') == 'dark' ) { // In dark mode
        if (graphEmpty) { // If there is no data for graph (grey)
            return 'rgb(153,153,153)';
        } else { // If the graph has data to use
            return ['rgb(128,66,255)', 'rgb(76,192,192)', 'rgb(255,24,104)'];
        }

    } else { // In light mode
        if (graphEmpty) { // No graph data (grey)
            return 'rgb(199, 199, 199)';
        } else { // Has graph data
            return ['rgb(155,246,255)', 'rgb(202,255,191)', 'rgb(255,173,173)'];
        }
    }
}


// Creates the graph for how many tasks of each kind an employee has for a particular project
async function fillProjEmpBreakdownTile(project_id) {
    // Creates a graph showing the completion, on track and overdue tasks for each employee
    fetch(`manager_resources/db_queries.php?type=emp_task_breakdown_proj&projectID=${project_id}`)
        .then(response => response.json())
        .then(data => {
            // Clearing the graph for when the dashboard is refreshed
            document.getElementById('projTileEmpTaskBreakdown').innerHTML = "";

            // Creating a canvas for the graph
            let canvasTasksDueCount = document.createElement('canvas');
            canvasTasksDueCount.id = 'canvas_proj_emp_due';
            canvasTasksDueCount.style.height = '100%'; 
            canvasTasksDueCount.style.width = '100%';
            projTileEmpTaskBreakdown.appendChild(canvasTasksDueCount);

            // Getting the data in a useable format for the stacked graph
            let employee_names = [];
            let completed = [];
            let onTrack = [];
            let overdue = [];
            data.forEach(employee => {
                employee_names.push(`${employee.firstName} ${employee.surname}`);
                completed.push(employee.completed);
                onTrack.push(employee.onTrack);
                overdue.push(employee.overdue);
            })
            let graphData;
            let graphOptions;
            // If there are no tasks in a project, have it display a greyed out filled graph
            if (onTrack.length == 0 && completed.length == 0 && overdue.length == 0) {
                graphData = {
                    labels: [""],
                    datasets: [
                        {
                            data: [1],
                            backgroundColor: getGraphColourScheme(true)
                        }, 
                        
                    ]
                };
                graphOptions = {
                    indexAxis: 'y',
                    plugins: {
                        legend: false,
                        tooltip: {
                            enabled: false
                        },
                        title: {
                            display: true,
                            text: 'No Employees or Tasks'
                        },
                    },
                    responsive: true,
                    scales: {
                        x: {
                            stacked: true,
                            ticks: {
                                stepSize: 1 // Forcing step size of 1 as tasks is a discrete number
                            }
                        },
                        y: {
                            stacked: true
                        }
                    }
                }

            // For when there is any task data for a project
            } else {
                colourScheme = getGraphColourScheme(false);
                graphData = {
                    labels: employee_names,
                    datasets: [
                        {
                            label: 'Completed',
                            data: completed,
                            backgroundColor: colourScheme[0],
                        }, 
                        {
                            label: 'On Track',
                            data: onTrack,
                            backgroundColor: colourScheme[1],
                        },
                        {
                            label: 'Overdue',
                            data: overdue,
                            backgroundColor: colourScheme[2],
                        }
                    ]
                };
                graphOptions = {
                    indexAxis: 'y',
                    plugins: {
                        title: {
                            display: true,
                            text: 'Employee Progress'
                        },
                    },
                    responsive: true,
                    scales: {
                        x: {
                            stacked: true,
                            ticks: {
                                stepSize: 1 // Forcing step size of 1 as tasks is a discrete number
                            }
                        },
                        y: {
                            stacked: true // Stacks the datasets
                        }
                    }
                };
            }

            const projEmpDueChart = new Chart(canvasTasksDueCount, {
                type: 'bar',
                data: graphData,
                options: graphOptions
            });
        });
}


// Creates a doughnut graph showing the number of tasks completed, on schedule or overdue for the whole project
async function fillProjProgressionTile(project_id) {
    fetch(`manager_resources/db_queries.php?type=tasks_overdue&projectID=${project_id}`)
        .then(response => response.json())
        .then(project => {
            // Clearing the graph for when data is refreshed
            document.getElementById('projTileTasksDue').innerHTML = "";

            // Creating a canvas for the graph
            let canvasTasksDueCount = document.createElement('canvas'); 
            canvasTasksDueCount.id = 'canvas_proj_overdue';
            canvasTasksDueCount.style.height = '100%'; 
            canvasTasksDueCount.style.width = '100%';
            projTileTasksDue.appendChild(canvasTasksDueCount);

            let graphdata;
            let graphOptions;
            // If there are no tasks in a project using a greyed out graph to indicate so
            if (project[0].completed == 0 && project[0].onTrack == 0 && project[0].overdue == 0) {
                graphdata = {
                    datasets: [{
                    data: [1],
                    backgroundColor: getGraphColourScheme(true),
                    borderWidth: 1
                }]}

                graphOptions = {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            enabled: false
                        },
                        title: {
                            display: true,
                            text: "No Projects Added"
                        }
                    },
                };
            // If there is some tasks in a project
            } else {
                graphdata = {
                    labels: ['Completed', 'On Track', 'Overdue'],
                    datasets: [{
                    label: 'Tasks',
                    data: [project[0].completed, project[0].onTrack, project[0].overdue],
                    backgroundColor: getGraphColourScheme(false),
                    borderWidth: 1
                }]};

                graphOptions = {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: "Overall Project Progression"
                        }
                    },
                };
            }

            const projChart = new Chart(canvasTasksDueCount, { // Creating the chart filled with returned data
                type: 'doughnut',
                data: graphdata,
                options: graphOptions
            });
        });
}


// Checks hte inputs for creating a new task are valid
function validateTaskInputs() {
    // Clearing the error message
    document.getElementById('newTaskErrorMessage').innerHTML = "";
    // Getting the value of all the inputs
    const empID = document.getElementById('newTaskEmpId').value;
    const taskName = document.getElementById('newTaskNameTxt').value;
    const duration = document.getElementById('newTaskDuration').value;
    const deadline = document.getElementById('newTaskDueDate').value;
    const highPriorityBool = document.getElementById('newTaskHighPriority').checked;
    let highPriority;
    if (highPriorityBool) {
        highPriority = 1;
    } else {
        highPriority = 0;
    }
    const description = document.getElementById('newTaskDescription').value;
    const projectID = document.getElementById('projectName').title.slice(12);
    let errorMsg = [];
    if (empID == "") {
        errorMsg.push('Select an Employee')
    }
    if (taskName == "") {
        errorMsg.push('Enter a task name');
    } else if (taskName.length > 30)  {
        errorMsg.push('Task Name needs to be < 30 characters');
    }
    if (isNaN(duration) || duration == "") {
        errorMsg.push('Enter valid duration');
    }
    if (deadline.length == 0) {
        errorMsg.push('Select a deadline');
    }
    if (description.length >  500) {
        errorMsg.push('Description length < 500');
    }
    // If there is no error message it will create the new task
    if (errorMsg == "") {
        fetch('manager_resources/db_write.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `type=createTask&taskName=${taskName}&userID=${empID}&duration=${duration}&deadline=${deadline}&highPriority=${highPriority}&description=${description}&projectID=${projectID}`
        })
        .then(response => response.json())
        .then(data => {
            refreshDashboard();
        });
    
    // Displaying the error message if there is one
    } else {
        errorMsg.forEach(error => {
            document.getElementById('newTaskErrorMessage').innerHTML += `${error}, `;
        })
    }
}
        

// Responsible for creating the employee cards
function create_cards_employees(obj_array) {
    const cardContainer = document.getElementById("card-container");
    // Looping through all employees in the current object array passed to it (allowing for sorting)
    for (let i = 0; i < obj_array.length; i++) {
        const card = document.createElement("div");
        card.className = "card";
        card.id = obj_array[i].userID;

        let employee_tasks_array = [];
        if (obj_array[i].taskNames != null){
            employee_tasks_array = obj_array[i].taskNames.split(",").map(task => task.trim()).join('<br>');
        }
      
        card.innerHTML = '<h5 class="card-header">' + obj_array[i].firstName + " " + obj_array[i].surname + '</h5><div class="card-text mt-2">Tasks:  <span class="badge rounded-pill text-bg-success">' + obj_array[i].completedTasksCount + ' Complete</span><span class="badge rounded-pill text-bg-danger">' + obj_array[i].notCompletedTasksCount + ' Incomplete</span>';

        card.innerHTML += '<div class="overflow-y-auto">' + employee_tasks_array + '</div></p>';

        card.addEventListener("click", function() {
            open_employee_dashboard(obj_array[i].userID);
        });

        cardContainer.appendChild(card);
    }
}


function open_employee_dashboard(userID) {
    // Clearing the page of everything (beside the navbar)
    parentDiv = document.getElementById('data_display_parent');
    parentDiv.innerHTML = ""; 
    document.getElementById("taskbar").style.display = "none";
    
    //Creating the container for the users name
    const userTitleDiv = document.createElement('div');
    userTitleDiv.id = 'userTitleDiv';
    parentDiv.appendChild(userTitleDiv);
    // Creating the container for the employee overview dashboard
    const dashboardContainerDiv = document.createElement('div');
    dashboardContainerDiv.id = 'dashboardContainerDiv';
    parentDiv.appendChild(dashboardContainerDiv);
    const dashboardDiv = document.createElement('div');
    dashboardDiv.id = 'dashboardDiv';
    dashboardContainerDiv.appendChild(dashboardDiv);
    
    fetch(`manager_resources/db_queries.php?type=emp_name&userID=${userID}`)
        .then(response => response.json())
        .then(data => {
            // Creating the return button and showing the users name
            // Arrow return left icon from bootstrap icons, https://icons.getbootstrap.com/icons/arrow-return-left/
            let topBarHtml = `<div class="row"><div class="col-auto"><button class="btn btn-outline-danger m" id="exitDashboardButton" onclick="exitDashboard()"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-return-left" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M14.5 1.5a.5.5 0 0 1 .5.5v4.8a2.5 2.5 0 0 1-2.5 2.5H2.707l3.347 3.346a.5.5 0 0 1-.708.708l-4.2-4.2a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 8.3H12.5A1.5 1.5 0 0 0 14 6.8V2a.5.5 0 0 1 .5-.5"/>
            </svg></button></div><div class="col"><h2 class="ms-3" id="projectName" title="User ID: ${userID}">${data[0].firstName} ${data[0].surname}</h2></div>`

            // If the user is a teamleader they have reduced permissions - can't see all manager features
            if (role == "teamLeader") {
                topBarHtml += `</div>`;
            } else {
                // Creating the knowledge admin and manager toggle, as well as delete employee button
                topBarHtml += `<div class="col-auto"><div class="form-check form-switch" style="float: right; margin-right: 10px">
                <input class="form-check-input" type="checkbox" role="switch" id="toggleKnowledgeAdmin" style="height: 30px; width: 55px" onclick="toggleKnowledgeAdmin(${userID})">
                <label class="form-check-label" for="toggleKnowledgeAdmin" style="font-size: 1.6em; margin-left: 10px;">Knowledge Admin</label>
                </div></div>
    
                <div class="col-auto"><div class="form-check form-switch" style="float: right; margin-right: 10px">
                <input class="form-check-input" type="checkbox" role="switch" id="toggleManagerRoleSwitch" style="height: 30px; width: 55px" onclick="toggleManagerRole(${userID})">
                <label class="form-check-label" for="toggleManagerRoleSwitch" style="font-size: 1.6em; margin-left: 10px;">Manager</label>
                </div></div>
                
                <div class="col-auto"><div style="float: right; margin-right: 10px"><button class="btn btn-danger" onclick="confirmdeleteUser(${userID})" style="width:50px; height:50px">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                </svg></button></div>
                </div>
                </div>`;
                // Trash icon from bootstrap icons, https://icons.getbootstrap.com/icons/trash/
            }
            userTitleDiv.innerHTML = topBarHtml;

            if (role == "teamLeader") {

            } else { // Setting the status of the toggles to true if they are managers / knowledge admins
                if (data[0].manager == 1) {
                    document.getElementById('toggleManagerRoleSwitch').checked = true;
                }
                if (data[0].knowledgeAdmin == 1) {
                    document.getElementById('toggleKnowledgeAdmin').checked = true;
                }
            }
    });

    // Creating the tiles for the dashboard
    let tileNames = ["empTileSummary", "empTileTasksDue", "empTileHighPriority"]
    tileNames.forEach(name => {
        let newTile = document.createElement('div');
        newTile.id = name;
        newTile.className = 'tile';
        dashboardDiv.appendChild(newTile);        
    });
    // Creating the large tile for the employee task table
    let newTile = document.createElement('div');
    newTile.id = "empTileViewAllTasks";
    newTile.className = 'large-tile';
    dashboardDiv.appendChild(newTile);


    fillTileHighestPriorityEmp(userID);
    fillTileTaskTableEmployee(userID);
    empFillDoughnutOverview(userID);
}


// Toggles a users role between manager and employee
function toggleManagerRole(userID) {
    // Gets the value of the toggle (what to change to)
    let changeTo;
    if (document.getElementById('toggleManagerRoleSwitch').checked) {
        changeTo = 1;
    } else {
        changeTo = 0;
    }

    fetch('manager_resources/db_write.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `type=toggleManagerRole&userID=${userID}&changeTo=${changeTo}`
    })
    .then(response => response.json())
    .then(data => {
        refreshDashboard();
    });
}


// Grants/revokes a users knowledge admin permissions
function toggleKnowledgeAdmin(userID) {
    // Gets what value to change to
    let changeTo;
    if (document.getElementById('toggleKnowledgeAdmin').checked) {
        changeTo = 1;
    } else {
        changeTo = 0;
    }

    fetch('manager_resources/db_write.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `type=toggleKnowledgeAdmin&userID=${userID}&changeTo=${changeTo}`
    })
    .then(response => response.json())
    .then(data => {
        refreshDashboard();
    });
}


// Used for the return button, exits the dashboard
function exitDashboard() {
    document.getElementById("taskbar").style.display = "flex";
    parentDiv = document.getElementById('data_display_parent');
    parentDiv.innerHTML = `<h1 id="title_dashboard_container">Projects</h1>
                           <div class="flex-container" id="card-container"></div>`;
    search_sort_process();
}


// Creates a graph for the employee overview dashboard on how many tasks are overdue, completed or on track as well sa the summary tile
async function empFillDoughnutOverview(userID) {
    fetch(`manager_resources/db_queries.php?type=emp_summary&userID=${userID}`)
        .then(response => response.json())
        .then(data => {
            // Setting the text for a users role
            let usersRole;
            if (data[0].manager == 1) {
                usersRole = "Manager";
            } else {
                usersRole = "Employee"
            }
            const tileSummary = document.getElementById('empTileSummary');
            tileSummary.innerHTML = `<h3>Overview</h3>
                                <table class="overviewTable centredElement">
                                <tr><td>Completed</td><td>${data[0].completed} tasks</td></tr>
                                <tr><td>On Track</td><td>${data[0].onTrack} tasks</td></tr>
                                <tr><td>Overdue</td><td>${data[0].overdue} tasks</td></tr>
                                <tr><td>Part Of</td><td>${data[0].partOf} projects</td></tr>
                                <tr><td>Registered</td><td>${data[0].registrationDate}</td></tr>
                                <tr><td>Role</td><td>${usersRole}</td></tr></table>`;
            
            // Creating the canvas for the graph
            const empTileTasksDue = document.getElementById('empTileTasksDue');
            empTileTasksDue.innerHTML = "";
            let canvasTasksDueCount = document.createElement('canvas');
            canvasTasksDueCount.id = 'canvas_proj_overdue';
            canvasTasksDueCount.style.height = '100%'; 
            canvasTasksDueCount.style.width = '100%';
            empTileTasksDue.appendChild(canvasTasksDueCount);

            // If an employee has no tasks, show a greyed out graph instead of an empty one
            if (data[0].completed == 0 && data[0].onTrack == 0 && data[0].overdue == 0) {
                graphdata = {
                    datasets: [{
                    data: [1],
                    backgroundColor: getGraphColourScheme(true),
                    borderWidth: 1
                }]}

                graphOptions = {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            enabled: false
                        },
                        title: {
                            display: true,
                            text: "No Projects Added"
                        }
                    },
                };
            // For when the employee has tasks
            } else {
                graphdata = {
                    labels: ['Completed', 'On Track', 'Overdue'],
                    datasets: [{
                    label: 'Tasks',
                    data: [data[0].completed, data[0].onTrack, data[0].overdue],
                    backgroundColor: getGraphColourScheme(false),
                    borderWidth: 1
                }]};

                graphOptions = {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: "Overall Project Progression"
                        }
                    },
                };
            }

            const projChart = new Chart(canvasTasksDueCount, {
                type: 'doughnut',
                data: graphdata,
                options: graphOptions
            });
        });
}


// Creates a table to show all tasks an employee has
async function fillTileTaskTableEmployee(userID) {
    fetch(`manager_resources/db_queries.php?type=emp_all_task_info&userID=${userID}`)
        .then(response => response.json())
        .then(data => {
            // Clears the tile for when data changes on refresh
            document.getElementById('empTileViewAllTasks').innerHTML = "";
            // Creating the table
            const table = document.createElement('table');
            const thead = document.createElement('thead');
            const tbody = document.createElement('tbody');
            tbody.id = "employeeTableBody";
            table.className = "taskTable";
            thead.className = "tableHeadStyle";

            // Creating the headers of the table
            const titles = ['Duration', 'Due Date', 'High Priority', 'Completed', ''];
            const tr = document.createElement('tr');

            const th = document.createElement('th');
            th.innerHTML = `<input type="search" class="form-control" id="searchTaskNameTableEmp" placeholder="Task" autocomplete="off" onkeyup="filterTaskTableEmp()"></input>`;
            tr.appendChild(th);

            titles.forEach(title => {
                const th = document.createElement('th');
                th.textContent = title;
                tr.appendChild(th);
            })
            thead.appendChild(tr);

            // Filling the table rows with data
            data.forEach(task => {
                const tr = document.createElement('tr');

                const td1 = document.createElement('td'); // Task name
                td1.textContent = task.name;
                tr.appendChild(td1);
                const td3 = document.createElement('td'); // Duration
                td3.textContent = task.timeEstimate;
                tr.appendChild(td3);
                const td4 = document.createElement('td'); // Deadline
                td4.textContent = task.deadline;
                tr.appendChild(td4);
                const td6 = document.createElement('td'); // Priority Switch
                td6.innerHTML = `<div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="prioritySwitch${task.taskID}" onclick="toggleTaskPriority(${task.taskID})"></div>`;
                tr.appendChild(td6);
                const td5 = document.createElement('td'); // Completion Switch
                td5.innerHTML = `<input class="form-check-input" type="checkbox" id="checkBoxTaskComplete${task.taskID}" onclick="toggleTaskComplete(${task.taskID})">`;
                tr.appendChild(td5);
                const td7 = document.createElement('td'); // Delete button
                // Trash icon from bootstrap icons, https://icons.getbootstrap.com/icons/trash/
                td7.innerHTML = `<button type="button" class="btn btn-secondary" onclick="deleteTask(${task.taskID})">
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                   <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
                                   <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                                   </svg></button>`;
                tr.appendChild(td7);

                tbody.appendChild(tr);
            });

            table.appendChild(thead);
            table.appendChild(tbody);
            empTileViewAllTasks.appendChild(table);
            
            // Toggling the completion and high priority switches if needed
            data.forEach(task => {
                if (task.isCompleted == 1) {
                    document.getElementById(`checkBoxTaskComplete${task.taskID}`).checked = true;
                }
                if (task.highPriority == 1) {
                    document.getElementById(`prioritySwitch${task.taskID}`).checked = true;
                }
            })
        });
}


// Displays a users highest priority tasks
async function fillTileHighestPriorityEmp(userID){
    fetch(`manager_resources/db_queries.php?type=high_priority_emp&userID=${userID}`)
    .then(response => response.json())
    .then(data => {
        // Clearing the tile for when the dashboard refreshes
        const empTileHighPriority = document.getElementById('empTileHighPriority');
        empTileHighPriority.innerHTML = "";

        const empPriorityList = document.createElement('ul');
        // If they have no due tasks then display that with a tile
        if (data.length == 0) {
            const empPriorityListItem = document.createElement('li');
            empPriorityListItem.innerHTML = "No tasks due";
            empPriorityListItem.className = "priorityLI";
            empPriorityList.appendChild(empPriorityListItem);

        // If there are tasks due
        } else {
            data.forEach(task => {
                // Creating the high priority item
                const empPriorityListItem = document.createElement('li');
                let priorityTaskHtml = `${task.name}`;
                if (task.highPriority == 1) {
                    priorityTaskHtml += `<span class='highPriorityText badge text-bg-primary ms-3'>High priority</span>`;
                }
                priorityTaskHtml += `<br>`
                // Deciding if a task is due in the future or past
                const daysUntilDeadline = daysUntil(task.deadline);
                if (daysUntilDeadline > 0) {
                    priorityTaskHtml += `Due: ${daysUntilDeadline} days`;
                } else if (daysUntilDeadline < 0) {
                    priorityTaskHtml += `Due: ${Math.abs(daysUntilDeadline)} days ago`;
                } else {
                    priorityTaskHtml += `Due: today`;
                }
                
                empPriorityListItem.innerHTML = priorityTaskHtml;
                empPriorityListItem.className = "priorityLI";
                empPriorityList.appendChild(empPriorityListItem);
            })
        }
        empTileHighPriority.innerHTML = `<h3>Highest Priority</h3>`;
        empTileHighPriority.appendChild(empPriorityList);
    });
}


// Open the dropdown menu for employees when clicked
function displayDropdownEmployees() {
    document.getElementById('dropdownMenu').classList.toggle("show");
}


// Filters the list of employee names based on the search by the user
function filterEmployeeList() {
    const textFilter = document.getElementById("dropdownSearchEmployees").value.toLowerCase();
    // Getting an array of all list items from within the dropdown
    const employeeList = Array.from(document.getElementById("dropdownMenuEmployees").getElementsByTagName("li"));
    employeeList.forEach(employee => {
        if (employee.textContent.toLowerCase().indexOf(textFilter) > -1) {
            employee.style.display = "";
        } else {
            employee.style.display = "none";
        }
    })
}


// Deletes a task from the database
function deleteTask(taskID){
    fetch('manager_resources/db_write.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `type=deleteTask&taskID=${taskID}`
    })
    .then(response => response.json())
    .then(data => {
        refreshDashboard();
    });
}


// Responsible for changing the state of a task being completed / uncompleted
function toggleTaskComplete(taskID) {

    // Gets what value to change the completion state to
    let changeTo;
    if (document.getElementById(`checkBoxTaskComplete${taskID}`).checked) {
        changeTo = 1;
    } else {
        changeTo = 0;
    }
    
    // Sending the POST request to change the database
    fetch('manager_resources/db_write.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `type=taskCompletionChange&taskID=${taskID}&changeTo=${changeTo}`
    })
    .then(response => response.json())
    .then(data => {
        refreshDashboard();
    });    
}


// Changes the priority of a task from low to high and high to low
function toggleTaskPriority(taskID) {

    let changeTo; // Getting the value of the checkbox
    if (document.getElementById(`prioritySwitch${taskID}`).checked) {
        changeTo = 1;
    } else {
        changeTo = 0;
    }
    
    // Sending the POST request to change the database
    fetch('manager_resources/db_write.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `type=taskPriorityChange&taskID=${taskID}&changeTo=${changeTo}`
    })
    .then(response => response.json())
    .then(data => {
        refreshDashboard();
    });
}


// Recreates data elements when called to keep updated with user changes
function refreshDashboard() {
    const title = document.getElementById('projectName').title;
    // For when the user dashboard is opened
    if (title.slice(0, 1) == ('U')) {
        const userID = title.slice(9);
        fillTileHighestPriorityEmp(userID);
        fillTileTaskTableEmployee(userID);
        empFillDoughnutOverview(userID);
    
    // For when the project dashboard is open
    } else {
        const project_id = title.slice(12);
        fillTileSummary(project_id);
        fillProjProgressionTile(project_id);
        fillProjEmpBreakdownTile(project_id);
        fillTileHighestPriorityProj(project_id);
        fillEmpNameDropdown(project_id);
        fillTileTaskTable(project_id);
    }
}


// Works out the number of days from / until a specific date
function daysUntil(date) {
    const todayDate = new Date();
    const comparisonDate = new Date(date);
    const difference = Math.round((comparisonDate - todayDate) / (24 * 3600 * 1000) )
    return difference;
}


// Getting the drop down menu
const drop_down_menu_team_leader = document.getElementById('dropdown_menu_employees_add_proj');


// Filling the dropdown menu with employee names
function fill_dropdown_menu(employee_options, dropdown_menu) {
    dropdown_menu.innerHTML = '';
    employee_options.forEach(employee => {
        const item = document.createElement('div');
        item.classList.add('dropdown-item');
        item.textContent = employee.firstName + ' ' + employee.surname;
        item.setAttribute('data-id', employee.userID);
        dropdown_menu.appendChild(item);
    });
}


// Fill the dropdown menu when first clicking Create Project
document.getElementById('btn_open_modal_create').addEventListener('click', function () {
    get_employees_name_and_id().then(employees_name_and_id_array => {
        fill_dropdown_menu(employees_name_and_id_array, drop_down_menu_team_leader);
    })
})


// Making the search feature responsive and filter down names (for adding a new project)
document.getElementById('search_employee_team_leader').addEventListener('input', function (e) {
    const search_entry = e.target.value.toLowerCase();

    get_employees_name_and_id().then(employees_name_and_id_array => {
        const filtered_employee_array = employees_name_and_id_array.filter(employee => {
            const employee_name = (employee.firstName.toLowerCase() + ' ' + employee.surname.toLowerCase());
            return employee_name.includes(search_entry);
        });
        fill_dropdown_menu(filtered_employee_array, drop_down_menu_team_leader);

    });

});


// Adding an event listener so when a employee name is clicked it is selected (for adding a new project)
drop_down_menu_team_leader.addEventListener('click', function (e) {
    if (e.target.matches('.dropdown-item')) {

        document.getElementById('dropdown_button_team_leader').textContent = e.target.textContent;
        document.getElementById('project_selected_team_leader_id').value = e.target.getAttribute('data-id');
      }
});


// Process the data received and check them, then call the function to send a sql query to create a new project
function create_new_project(){
    const teamLeader = document.getElementById('project_selected_team_leader_id').value;
    const name = document.getElementById('project_name').value;
    const description = document.getElementById('project_description').value;
    const deadline = document.getElementById('project_deadline').value;

    // If some value is empty, display the error message
    if (name == ""){
        document.getElementById("potential_error_message_for_new_project").innerHTML = "Project name cannot be empty!";
    } else if (name.length > 40) {
        document.getElementById("potential_error_message_for_new_project").innerHTML = "Project name must be under 40 characters";
    } else if (deadline == ""){
        document.getElementById("potential_error_message_for_new_project").innerHTML = "Project deadline needed!";
    } else if (teamLeader == ""){
        document.getElementById("potential_error_message_for_new_project").innerHTML = "Team leader needed!";
    } else if (deadline.length > 500) {
        document.getElementById("potential_error_message_for_new_project").innerHTML = "Description must be under 500 characters";
    } else {
        fetch('manager_resources/db_write.php', {
            method: 'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `type=create_new_project&name=${name}&description=${description}&deadline=${deadline}&teamLeader=${teamLeader}`
        })
        .then(response => response.text())
        .then(data => {
            console.log(data);
            search_sort_process();
        });

        // Set the input entrys to empty after a new project been successfully created
        document.getElementById("potential_error_message_for_new_project").innerHTML = "";
        document.getElementById('project_name').value = "";
        document.getElementById('project_description').value = "";
        document.getElementById('project_deadline').value = "";
        document.getElementById('project_selected_team_leader_id').value = "";
        document.getElementById('dropdown_button_team_leader').innerText = "Select Employee";
        document.getElementById("btn-close-create-project-window").click();
    }
}

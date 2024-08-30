// to-do list variables
let toDoFilter = "";
let taskFilter = "";
let toDos = [];

// task variables
let currentTaskID = 0;
// variable to fix bug where after task has been deleted it tried to add the event listener for the li again
// bug caused as the delete button is part of the li which has the event listener
// stops li event listener code running twice, hence fixing the bug
let taskKilled = false;

// current project variable
let currentProjectID = 0;


const showToDos = () => {
    /*for each To-do item in the array, 
    show it in a list on the page, depending on the user selections
    of task and project
    */
    let toDosToDisplay = [];
    let tickedToDosToDisplay = [];
    // Loop through all To-dos and check if they should be shown:
    for (let i = 0; i < toDos.length; i++) {
        // filter based on search input
        if (
            toDos[i].detail.toLowerCase().search(toDoFilter.toLowerCase()) != -1
        ) {
            if (toDos[i].taskID != null) {
                //check if To-do is part of a task
                /*If the To-do items associated with all of a user's
                tasks that are associated with a specific one
                of their projects are being displayed:*/
                if (currentTaskID == 0 && currentProjectID != 0) {
                    /* Loop through the tasks in tasksToDisplay (this array
                    will only contain tasks that are part of the selected
                    project to be displayed) and check whether the To-do is part of each. 
                    If it is, display the To-do.*/
                    for (let j = 0; j < tasksToDisplay.length; j++) {
                        if (
                            toDos[i].taskID == tasksToDisplay[j].ID
                        ) {
                            /*all tasks in tasksToDisplay will be part of the project selected
                            (as only the tasks that are relevant to the selected project are fetched)
                            so only the task ID of each task needs to be compared with each to-do item (this is done in line 95)*/
                            if (toDos[i].isCompleted) {
                                tickedToDosToDisplay.push(toDos[i]);
                            } else {
                                toDosToDisplay.push(toDos[i]);
                            }
                        }
                    }
                } else if (
                    /*If the To-do items associated with all of a user's
                    tasks are being displayed*/
                    (currentProjectID == 0 && currentTaskID == 0) ||
                    /*If the To-do items associated with one of a user's
                    tasks are being displayed*/
                    toDos[i].taskID == currentTaskID
                ) {
                    if (toDos[i].isCompleted) {
                        tickedToDosToDisplay.push(toDos[i]);
                    } else {
                        toDosToDisplay.push(toDos[i]);
                    }
                }
            } else if (currentTaskID == 0 && currentProjectID == 0) {
                /*If the To-do isn't part of a task and if all To-dos are being
                displayed, show this To-do*/
                /*Appears to be covered by previous statement - this can be removed
               if null checking is removed
               (null checking isn't currently used,
               task IDs of 0 are being used when a To-do item
               isn't associated with a task*/
                if (toDos[i].isCompleted) {
                    tickedToDosToDisplay.push(toDos[i]);
                } else {
                    toDosToDisplay.push(toDos[i]);
                }
            }
        }
    }

    // call function to create HTML list of To-do items and display it:
    let toDoItemsHTML = createToDoList(toDosToDisplay);
    let tickedToDoItemsHTML = createTickedToDoList(tickedToDosToDisplay);
    document.querySelector("#toDoList").innerHTML = toDoItemsHTML;
    document.querySelector("#tickedToDoList").innerHTML = tickedToDoItemsHTML;
    // make each toDo clickable for removal:
    setRemovableItems();
    uncompleteItems();
};

let tasksToDisplay = [];
let completedTasksToDisplay = [];

const fetchAllTasksForDisplay = () => {
    const completedTaskFetchParams = {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:
            "reason=" +
            encodeURIComponent("displayCompletedTaskListAllProjects"),
    };

    fetch("productivityResources/tasks.php", completedTaskFetchParams)
        .then((response) => response.json())
        .then((responseData) => {
            completedTasksToDisplay = responseData;

        });

    const taskFetchParams = {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "reason=" + encodeURIComponent("displayTaskListAllProjects"),
    };

    fetch("productivityResources/tasks.php", taskFetchParams)
        .then((response) => response.json())
        .then((responseData) => {
            tasksToDisplay = responseData;

            showTasks();
        });
};
async function fetchTasksForDisplay(projectID) {
    //the function is async so that it returns a Promise object
    const taskFetchParams = {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:
            "reason=" +
            encodeURIComponent("displayTaskList") +
            "&projectID=" +
            encodeURIComponent(projectID),
    };

    //await on below lines to wait for Promise resolution
    const response = await fetch(
        "productivityResources/tasks.php",
        taskFetchParams
    );
    const responseData = await response.json();
    /*once data fetched and JSON parsed, update tasksToDisplay
    and display the fetched data*/
    tasksToDisplay = responseData;
    showTasks();
}

async function fetchToDos() {
    const toDoFetchParams = {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },

    };
    /*fetch to-dos from the database, parse the JSON, update the to-dos array
    with the newly retrieved data and update the to-do list on the page by
    calling showToDos*/
    const response = await fetch('productivityResources/to-dos.php', toDoFetchParams);
    const responseData = await response.json();
    toDos = responseData;
    showToDos();
}
fetchToDos();
fetchAllTasksForDisplay();
const showTasks = () => {
    /*for each task item in the arrays, 
    show it in a list on the page, depending on the user
    selection of which project to display*/

    //Task IDs start at 1, 0 represents all tasks

    /* Call function to create HTML list of tasks and display it,
    along with the option to view To-dos relating to all tasks*/
    let taskItems =
        "<li class='task' id='task-0'><p>All Tasks</p></li>" +
        createTaskList(tasksToDisplay, "task");
    document.querySelector("#taskList").innerHTML = taskItems;
    let completedTaskItems = "";
    for (let i = 0; i < completedTasksToDisplay.length; i++) {
        completedTaskItems =
            completedTaskItems +
            "<li>" +
            completedTasksToDisplay[i].name +
            "</li>";
    }
    if (
        currentProjectID == 0 &&
        taskFilter === "" &&
        completedTasksToDisplay.length > 0
    ) {
        document.querySelector("#completedTaskList").innerHTML =
            completedTaskItems;
        document.querySelector("#completedTasks").style.display = "block";
    } else {
        document.querySelector("#completedTasks").style.display = "none";
    }
    // Make tasks clickable to select which To-dos to display:
    setTasksClickable();
    if (currentTaskID == 0) {
        document.getElementById("task-0").innerHTML =
            "<div style='text-decoration: underline !important;\
        text-underline-offset: 2px !important;\
        text-decoration-thickness: 3px !important;\
        text-decoration-color: #c6aa34 !important;'>" +
            document.getElementById("task-0").innerHTML +
            "</div>";
    }
    showToDos();
};

const fetchProjects = () => {
    /*set parameters for the Fetch request to Fetch project names and IDs relevant to the user
    from the PHP file by sending the reason for the request (to display the list of projects)
    in the body of the POST request*/
    const projectFetchParams = {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "reason=" + encodeURIComponent("displayProjectList"),
    };
    fetch("productivityResources/projects.php", projectFetchParams)
        .then((response) => response.json())
        .then((responseData) => {
            projectsToDisplay = responseData;
            showProjects();
        });
    /*the above 2 lines cause the up-to-date project list to be displayed on the page
    by assigning the array of project items received to the projectsToDisplay variable
    and calling showProjects */
};
fetchProjects();

const fetchProjectDetails = (projectID) => {
    /*set parameters for the Fetch request to Fetch the details of the project that the 
    user requested details for by sending the reason for the Fetch and the 
    ID of the requested project in the body of the POST request*/
    const projectFetchParams = {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:
            "reason=" +
            encodeURIComponent("displayProjectDetails") +
            "&projectID=" +
            encodeURIComponent(projectID),
    };
    fetch("productivityResources/projects.php", projectFetchParams)
        .then((response) => response.json())
        .then((responseData) => {
            //set each part of the relevant modal element content to the received data
            document.querySelector("#projectModalLabel").innerHTML =
                responseData[0].name;
            document.querySelector("#projectModalBody").innerHTML =
                "<ul><li>" +
                responseData[0].description +
                "</li><li>Created: " +
                responseData[0].dateCreated +
                "</li><li>Deadline: " +
                responseData[0].deadline +
                "</li><li>Team Leader: " +
                responseData[0].firstName +
                " " +
                responseData[0].surname +
                "</li></ul>";
        });
};

const fetchTaskDetails = (taskID) => {
    /*set parameters for the Fetch request to Fetch the details of the task that the 
    user requested details for by sending the reason for the Fetch and the 
    ID of the requested task in the body of the POST request*/
    const taskFetchParams = {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:
            "reason=" +
            encodeURIComponent("displayTaskDetails") +
            "&taskID=" +
            encodeURIComponent(taskID),
    };
    fetch("productivityResources/tasks.php", taskFetchParams)
        .then((response) => response.json())
        .then((responseData) => {
            //set each part of the relevant modal element content to the received data
            document.querySelector("#taskModalLabel").innerHTML =
                responseData[0].name;
            document.querySelector("#taskModalBody").innerHTML =
                "<ul><li>" +
                responseData[0].description +
                "</li><li>Created: " +
                responseData[0].dateCreated +
                "</li><li>Deadline: " +
                responseData[0].deadline +
                "</li><li>Estimated Time: " +
                responseData[0].timeEstimate +
                " person hours</li></ul>";
        });
};

let projectsToDisplay;
const showProjects = () => {
    /*for each project item in the projectsToDisplay array, 
    show it in a list on the page*/
    /* Call function to create HTML list of projects and display it
    , along with the option for all projects*/
    let projectItems =
        "<li class='project' id='project-0'><p>All Projects</p></li>" +
        createProjectList(projectsToDisplay);
    document.querySelector("#projectList").innerHTML = projectItems;
    if (currentProjectID == 0) {
        document.querySelector("#project-" + currentProjectID).innerHTML =
            "<div style='text-decoration: underline !important;\
        text-underline-offset: 2px !important;\
        text-decoration-thickness: 3px !important;\
        text-decoration-color: #c6aa34 !important;'>" +
            document.querySelector("#project-" + currentProjectID).innerHTML +
            "</div>";
    }
    // Make projects clickable to select which To-dos to display:
    setProjectsClickable();
    setTaskSelectionOptions(); /*allow To-dos to be associated
    with a task on creation*/
};

const setRemovableItems = () => {
    /*Set the To-do items as clickable to remove the To-do
    item from the array and update the page to reflect
    this by calling showToDos()*/
    // Get all to do items
    let removableItem = document.querySelectorAll(".toDoItem");
    // Set up event listener for each To-do item so that they can be removed:
    for (let i = 0; i < removableItem.length; i++) {
        removableItem[i].addEventListener("click", () => {
            //get ID of To-do clicked:
            let IDToRemove = removableItem[i].getAttribute("id");
            IDToRemove = IDToRemove.slice(5); //get rid of "to-do" from ID text from HTML
            // get checkbox associated with item
            let checkbox = document.querySelector(
                "#toDo-" + IDToRemove + " input"
            );
            const toDoRemovalFetchParams = {
                method: "POST",
                headers: {
                    "Content-Type":
                        "application/x-www-form-urlencoded",
                },
                body:
                    "to-doID=" +
                    encodeURIComponent(IDToRemove),

            };
            //mark to-do as complete in database
            fetch(
                "productivityResources/to-doCompletion.php",
                toDoRemovalFetchParams
            )
                .then((response) => response.text()) 
                .then((responseData) => {

                    if (responseData != "true") {
                        alert(
                            "This to-do item could not be marked as complete. Please try again."
                        );
                        return;//end function
                    }
                    /*once success response received, check the checkbox
                    for the to-do and start the fade animation
                    */
                    for (let j = 0; j < toDos.length; j++) {
                        if (toDos[j].ID == IDToRemove) {
                            //find To-do which matches this ID
                            checkbox.checked = true;
                            fadeElement(
                                document.getElementById("toDo-" + IDToRemove),
                                0,
                                -0.1
                            );
                            toDos[j].isCompleted = true; /*set ticked attribute
                            for the to-do on the client-side*/
                        }
                    }

                })


            // run showToDos after fade has finished
            setTimeout(showToDos, 400);
        });
    }
};

const uncompleteItems = () => {
    /* Set all ticked to-do items as
    clickable to add it back to the
    incomplete to-dos list */
    // Get all ticked to do items
    let tickedItem = document.querySelectorAll(".tickedToDoItem");
    let checkbox = document.querySelectorAll(".tickedToDoItem input");
    // Set up event listener for each To-do item so that they can be removed:
    for (let i = 0; i < tickedItem.length; i++) {
        checkbox[i].addEventListener("click", () => {
            //get ID of To-do clicked:
            let IDToAdd = tickedItem[i].getAttribute("id");
            IDToAdd = IDToAdd.slice(5); //get rid of "to-do" from ID text from HTML
            const toDoAddFetchParams = {
                method: "POST",
                headers: {
                    "Content-Type":
                        "application/x-www-form-urlencoded",
                },
                body:
                    "to-doID=" +
                    encodeURIComponent(IDToAdd),

            };
            //set to-do as incomplete in database:
            fetch(
                "productivityResources/to-doUncompletion.php",
                toDoAddFetchParams
            )
                .then((response) => response.text()) 
                .then((responseData) => {

                    if (responseData != "true") {
                        alert(
                            "This to-do item could not be marked as complete. Please try again."
                        );
                        return //end function
                    }
                    /*once success response received, uncheck the checkbox
                    for the to-do and start the fade animation
                    */
                    checkbox[i].checked = false;
                    for (let j = 0; j < toDos.length; j++) {
                        if (toDos[j].ID == IDToAdd) {
                            fadeElement(
                                document.getElementById("toDo-" + IDToAdd),
                                0,
                                -0.1
                            );
                            //find To-do which matches this ID
                            toDos[j].isCompleted = false; /*set ticked attribute
                    on the client side*/
                        }
                    }

                })

            // run showToDos after fade has finished
            setTimeout(showToDos, 400);
        });
    }
};

const setTasksClickable = () => {
    /*Set each task so that clicking on a task will cause it to be underlined
    and update the currentTaskID variable so that the relevant Tasks are shown.*/
    let clickableTask = document.querySelectorAll(".task");
    // Set event listener for each task so that filters can be applied:
    for (let i = 0; i < clickableTask.length; i++) {
        clickableTask[i].addEventListener("click", () => {
            // Check if delete button has just been pressed for this task
            //or if "All tasks has been selected"
            if (taskKilled !== clickableTask[i].getAttribute("id").slice(5)) {
                currentTaskID = clickableTask[i].getAttribute("id");
                currentTaskID = currentTaskID.slice(5);
                showTasks();
                // Underline active filter for task that has been selected
                if (i == 0) {
                    document.querySelector("#task-" + currentTaskID).innerHTML =
                        "<div style='text-decoration: underline !important;\
                        text-underline-offset: 2px !important;\
                        text-decoration-thickness: 3px !important;\
                        text-decoration-color: #c6aa34 !important;'>" +
                        document.querySelector("#task-" + currentTaskID)
                        .innerHTML +
                        "</div>";
                } else {
                    document.querySelector("#task-" + currentTaskID).innerHTML =
                        "<div style='text-decoration: underline !important;\
                        text-underline-offset: 2px !important;\
                        text-decoration-thickness: 3px !important;\
                        text-decoration-color: #c6aa34 !important;'>" +
                        // Add button to open task modal
                        document.querySelector("#task-" + currentTaskID)
                            .innerHTML +
                        "<button type='button' class='btn btn-primary btn-sm' style='margin-bottom:20px;' data-bs-toggle='modal' data-bs-target='#taskModal'>\
                            Task Details\
                            </button>";
                    fetchTaskDetails(currentTaskID);
                    document
                        .querySelector("#removeTask")
                        .addEventListener("click", () => {

                            let IDofTaskToRemove = currentTaskID;
                            //fetch request here
                            const taskRemovalFetchParams = {
                                method: "POST",
                                headers: {
                                    "Content-Type":
                                        "application/x-www-form-urlencoded",
                                },
                                body:
                                    "taskID=" +
                                    encodeURIComponent(IDofTaskToRemove),
                            };

                            fetch(
                                "productivityResources/taskCompletion.php",
                                taskRemovalFetchParams
                            )
                                .then((response) => response.text()) 
                                .then((responseData) => {
                                    if (responseData != "true") {
                                        alert(
                                            "This task could not be marked as complete. Please try again."
                                        );
                                    }
                                    if (currentProjectID != 0) {
                                        //if "All Projects" is not selected
                                        /*fetch projects again - set currentProjectID to 0 if
                                        user no longer has any tasks on the previously
                                        selected project, and keep it the same otherwise*/

                                        fetchTasksForDisplay(
                                            currentProjectID
                                        ).then(() => {
                                            /*wait until tasks have been fetched before
                                            executing this function by using .then*/
                                            if (tasksToDisplay.length == 0) {
                                                /*if there are no longer any incomplete tasks
                                                associated with the currently selected project,
                                                display tasks associated with all projects*/
                                                currentProjectID = 0;
                                                fetchAllTasksForDisplay();
                                            }
                                            fetchProjects();
                                        });
                                    } else {
                                        /*if all projects were being displayed,
                                         fetch all projects and then fetch all tasks*/
                                        fetchProjects();
                                        currentTaskID = 0;
                                        //underline "All tasks":
                                        document.querySelector(
                                            "#task-0"
                                        ).innerHTML =
                                            "<div style='text-decoration: underline !important;\
                                            text-underline-offset: 2px !important;\
                                            text-decoration-thickness: 3px !important;\
                                            text-decoration-color: #c6aa34 !important;'>" +
                                            document.querySelector("#task-0")
                                            .innerHTML +
                                            "</div>";
                                        fetchAllTasksForDisplay();
                                    }

                                });
                        });
                }
            } else {
                taskKilled = false;
            }
        });
    }
};
const setProjectsClickable = () => {
    /*Set each project so that clicking on a project will cause it to be underlined
    and update the currentProjectID variable and initialise 
    currentTaskID to 0 so that the relevant To-dos are shown.*/
    let clickableProject = document.querySelectorAll(".project");
    for (let i = 0; i < clickableProject.length; i++) {
        clickableProject[i].addEventListener("click", () => {
            // Set current project
            /*Get ID of project clicked by clearing 'project' text from HTML <li> element
            ID*/
            currentProjectID = clickableProject[i].getAttribute("id").slice(8);
            currentTaskID = 0;
            if (currentProjectID != 0) {
                fetchTasksForDisplay(currentProjectID);
            } else {
                fetchAllTasksForDisplay();
            }
            // Refresh all lists
            showProjects();
            // Underline active filters
            if (currentProjectID == 0) {
                document.querySelector(
                    "#project-" + currentProjectID
                ).innerHTML =
                    "<div style='text-decoration: underline !important;\
                    text-underline-offset: 2px !important;\
                    text-decoration-thickness: 3px !important;\
                    text-decoration-color: #c6aa34 !important;'>" +
                    document.querySelector("#project-" + currentProjectID)
                        .innerHTML +
                    "</div>";
            } else {
                document.querySelector(
                    "#project-" + currentProjectID
                ).innerHTML =
                    "<div style='text-decoration: underline !important;\
                    text-underline-offset: 2px !important;\
                    text-decoration-thickness: 3px !important;\
                    text-decoration-color: #c6aa34 !important;'>" +
                    document.querySelector("#project-" + currentProjectID)
                        .innerHTML +
                    "</div>" +
                    "<button type='button' class='btn btn-primary btn-sm' style='margin-bottom:20px;' data-bs-toggle='modal' data-bs-target='#projectModal'>\
                        Project Details\
                    </button>";
                const id = currentProjectID;
                fetchProjectDetails(id);
            }
        });
    }
};
async function addToDo() {
    /*create a new To-do item object, add this to the array, 
    and update the page to show the new item*/
    let detail = document.querySelector("#newToDoDetail").value;
    // Disallow duplicate To-do items:
    for (let i = 0; i < toDos.length; i++) {
        if (detail == toDos[i].detail) {
            alert("To-do item already exists");
            return;
        }
    }
    // Disallow empty To-do descriptions
    if (detail != "") {
        //allow added To-dos to be associated with a given task:
        let associatedTask = document.querySelector("#associatedTask").value;
        //fetch request here
        const toDoCreationParams = {
            /*put the ID of the associated task and detail of the to-do
            that is being added in the body of the POST request*/
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body:
                "taskID=" + associatedTask + "&detail=" + detail,
        };
        //await on below lines to wait for Promise resolution
        //make fetch request to add the to-do item
        const response = await fetch('productivityResources/to-doAddition.php', toDoCreationParams);
        const responseData = await response.text();
        if (responseData != "true") {
            alert(
                "This to-do item could not be added. Please try again."
            );
        }
        document.querySelector("#newToDoDetail").value = "";
        fetchToDos();/*update the to-do list from the database to
        show the new to-do item*/
    } else {
        alert("Cannot add unnamed item");
    }
};
const createToDoList = (list) => {
    //takes list of To-do objects as input
    //and puts relevant ID of each To-do into ID attribute
    //then returns HTML list of To-do items
    let formattedList = "";
    if (list.length == 0) {
        return "<p>Your To-do list is empty for this selection.</p>";
    }
    for (let itemNumber = 0; itemNumber < list.length; itemNumber++) {
        formattedList +=
            "<li class='toDoItem' id='toDo-" +
            list[itemNumber].ID +
            "'><input class='form-check-input' type='checkbox' value='' style='display: inline-block; margin-right: 5px; cursor: pointer;'><p style='display: inline-block;'>" +
            list[itemNumber].detail +
            "</p>" +
            "</li>";
    }
    return formattedList;
};

const updateToDoSearchList = () => {
    // get input from search bar
    toDoFilter = document.querySelector("#toDoSearch").value;
    // remove any regex characters
    toDoFilter = regExEscape(toDoFilter);
    // update to-do list
    showToDos();
};

const updateTaskSearchList = () => {
    // get input from search bar
    taskFilter = document.querySelector("#taskSearch").value;
    // remove any regex characters
    taskFilter = regExEscape(taskFilter);
    // update to-do list
    showTasks();
};

const regExEscape = (str) => {
    /* stops string affecting regex pattern by escaping any regex quantifier */
    // array of regex characters
    regExChars = [
        "\\",
        "[",
        "]",
        "(",
        ")",
        "|",
        "^",
        ".",
        "+",
        "*",
        "?",
        "{",
        "}",
        "$",
    ];
    // for each character in array, escape all occurrences
    for (i = 0; i < regExChars.length; i++) {
        str = str.replaceAll(regExChars[i], "\\" + regExChars[i]);
    }
    // return escaped string
    return str;
};

const createTickedToDoList = (list) => {
    //takes list of To-do objects as input
    //and puts relevant ID of each To-do into ID attribute
    //then returns HTML list of To-do items
    let formattedList = "";
    if (list.length == 0) {
        return "<p>You have no To-do items that are marked as complete for this selection.</p>";
    }
    for (let itemNumber = 0; itemNumber < list.length; itemNumber++) {
        formattedList +=
            "<li class='tickedToDoItem' id='toDo-" +
            list[itemNumber].ID +
            "'><input class='form-check-input' type='checkbox' value='' style='display: inline-block; margin-right: 5px; cursor: pointer;' checked><p style='display: inline-block;'>" +
            list[itemNumber].detail +
            "</p>" +
            "</li>";
    }
    return formattedList;
};
const createTaskList = (list) => {
    //takes list of task objects as input
    // and puts relevant ID of each task into its ID attribute
    //then returns HTML list of Task items
    let formattedList = "";
    if (list.length > 0) {
        for (let itemNumber = 0; itemNumber < list.length; itemNumber++) {
            if (
                list[itemNumber].name
                    .toLowerCase()
                    .search(taskFilter.toLowerCase()) != -1
            ) {
                if (list[itemNumber].highPriority == "1") {
                    //Display a badge for high priority tasks:
                    formattedList +=
                        "<li class='task highPriority' id='task-" +
                        list[itemNumber].ID +
                        "'><p>" +
                        list[itemNumber].name +
                        "<span class='highPriorityText badge text-bg-primary'>High priority</span></p></li>";
                } else {
                    formattedList +=
                        "<li class='task' id='task-" +
                        list[itemNumber].ID +
                        "'><p>" +
                        list[itemNumber].name +
                        "</p></li>";
                }
            }
        }
    }
    return formattedList;
};
const createProjectList = (list) => {
    //takes list of project objects as input
    //puts relevant ID of project into its ID attribute
    //then returns HTML list of project items
    let formattedList = "";
    if (list.length > 0) {
        for (let itemNumber = 0; itemNumber < list.length; itemNumber++) {
            formattedList +=
                "<li class='project' id='project-" +
                list[itemNumber].ID +
                "'><p>" +
                list[itemNumber].name;
        }
    }
    return formattedList;
};
const setTaskSelectionOptions = () => {
    const taskFetchParams = {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "reason=" + encodeURIComponent("displayTaskNames"),
    };

    fetch("productivityResources/tasks.php", taskFetchParams)
        .then((response) => response.json())
        .then((responseData) => {

            // Set first option to "none"
            let options = "<option value='none'>None</option>";
            for (let i = 0; i < responseData.length; i++) {
                options +=
                    "<option value='" +
                    responseData[i].ID +
                    "'>" +
                    responseData[i].name +
                    "</option>";
            }
            // Set options for each available task
            document.getElementById("associatedTask").innerHTML = options;
        });
};

const fadeElement = (element, targetOpacity, step) => {
    // Ensure targetOpacity is between 0 and 1
    if (targetOpacity > 1) {
        targetOpacity = 1;
    } else if (targetOpacity < 0) {
        targetOpacity = 0;
    }

    // end function if step is 0
    if (step == 0) {
        return null;
    }

    // get current opacity
    let opacity = parseFloat(getComputedStyle(element).opacity);

    // check if target opacity is acheivable
    if (
        (step > 0 && targetOpacity < opacity) ||
        (step < 0 && targetOpacity > opacity)
    ) {
        return null;
    }

    // execute animation
    let id = setInterval(fadeFrame, 10);

    // animation code
    function fadeFrame() {
        // check if step is positive or negatives
        if (step > 0) {
            // check if target has been reached, if not increment
            if (opacity >= targetOpacity) {
                opacity = targetOpacity;
                element.style.opacity = opacity;
                clearInterval(id);
            } else {
                opacity += step;
                element.style.opacity = opacity;
            }
        } else {
            if (opacity <= targetOpacity) {
                opacity = targetOpacity;
                element.style.opacity = opacity;
                clearInterval(id);
            } else {
                opacity += step;
                element.style.opacity = opacity;
            }
        }
    }
};
async function showArchivedToDos() {
    const archivedToDoFetchParams = {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }
    /*send a POST request to the relevant PHP file to fetch the user's to-do
    item details and completion dates for items that have been completed for at least 7 days*/
    let response = await fetch("productivityResources/to-doArchiveDisplay.php", archivedToDoFetchParams);
    let responseData = await response.json();
    let HTMLListOfArchivedToDos;
    if (responseData.length == 0) {
        HTMLListOfArchivedToDos = "There are no items to display.";
    }
    else {
        HTMLListOfArchivedToDos = "<ul>";
        /*once the data has been received, put it into an HTML list and display
        it in the archive modal*/
        //Display all of the user's archived to-do items
        for (let i = 0; i < responseData.length; i++) {
            HTMLListOfArchivedToDos += "<li>" + responseData[i].detail + ", completed on " + responseData[i].completionDate + "</li>";
        }
        HTMLListOfArchivedToDos += "</ul>";
    }

    document.querySelector("#to-doArchiveModalBody").innerHTML = HTMLListOfArchivedToDos;
}


// Add event listener to form submission button:
document.querySelector("#addToDo").addEventListener("click", addToDo);
// Add event listeners for to-do and task search bars
document
    .querySelector("#toDoSearch")
    .addEventListener("input", updateToDoSearchList);
document
    .querySelector("#taskSearch")
    .addEventListener("input", updateTaskSearchList);
//Add event listener to to-do archive viewing button:
document.querySelector("#to-doArchiveModalButton").addEventListener("click", showArchivedToDos);


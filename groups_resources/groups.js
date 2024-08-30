let currentGroup = "";
// Getting the drop down menu
const dropdown_menu_users = document.getElementById('dropdown_menu_users');
//function to make the group list when the page is first loaded
function makeGroupList(search) {
  //used to generate the group list from the group array - map runs a for loop of the function specified on everything in the array
  $(document).ready(function () {
    groups = [[]];
    $.ajax({
      dataType: "json",
      url: "groups_resources/getGroups.php",
      success: function (data) {
        $.each(data, function (key, val) {
          if (typeof(val[0]) != "undefined" && typeof(val[1]) != "undefined"){
          //check the seach is empty or the word contains the search input
          if (val[0].includes(search) || search == ""){
          groups.push(val);
          }
        }
        });
        if (groups.length == 1) {
          //display that no groups are found
          document.getElementById("Group-List").innerHTML = "No groups found";
        } else {
          document.getElementById("Group-List").innerHTML = groups.map(generateList).join(' ');
          //takes in a item in an array and returns the code to put i as an item in a list
          function generateList(item) {
            if (typeof(item[0]) != "undefined" && typeof(item[1]) != "undefined"){
            //class = 'classGroups' is used to group all items in the group list so they can be found by the setListPositions function
            return "<li> <p style='display:inline-block;' class='ClassGroups'>" + item[0] + "</p>" + "<button style='margin-left: 5px;' class='deleteGroup btn btn-sm btn-danger' id = "+item[1]+">Delete</button> </li>";
            }
          };
          makeGroupsClickable();
          makeGroupsDeleteClickable();
        }
      }
    });
  });
}

//loops through all of the items in the group list and makes them clickable
function makeGroupsClickable() {
  let groupListItems = document.querySelectorAll(".ClassGroups");
  for (let i = 0; i < groupListItems.length; i++) {
    groupListItems[i].addEventListener("click", () => {
      makeUserList(groupListItems[i].innerHTML, "");
      document.getElementById("newUserSearch").style.display = "block";
      document.getElementById("userText").style.display = "none";
      clickedgroup = i;
    }
    )
  }
}

//loops through all of the group delete buttons and makes them clickable
function makeGroupsDeleteClickable() {
  let groupDeleteListItems = document.querySelectorAll(".deleteGroup");
  for (let i = 0; i < groupDeleteListItems.length; i++) {
    groupDeleteListItems[i].addEventListener("click", () => {
      let groupIDDelete = groupDeleteListItems[i].id;
      let confirmMsg = "Are you sure you want to delete this group";
      if (confirm(confirmMsg) == true) {
      //deleteGroups.php attempts to delete the clicked group
      $.ajax({
        dataType: "json",
        url: "groups_resources/deleteGroups.php",
        data: {
          groupID: groupIDDelete
        },
        success: function (data) {
          $.each(data, function (key, val) {
            console.log(val);
            if (val == "true"){
              alert("group has been deleted");
            } else{
              alert("error group has not been deleted");
            }
          });
            //refresh the group list
          makeGroupList("");
        }
      });
    }
    }
    )
  }
}

//finds the add group form by its ID
const groupAddForm = document.getElementById("NewGroupForm");
//adds an event listener for the add group submit event
groupAddForm.addEventListener("submit", function (event) {
  event.preventDefault();
  //get the value from the form
  const AddNewGroup = document.getElementById("AddNewGroup").value;
  //ajax request to sendGroup.php passing AddNewGroup and getting back true or false
  $.ajax({
    dataType: "json",
    url: "groups_resources/sendGroups.php",
    method: "get",
    data: { groupName: AddNewGroup},
    success: function (data) {
      $.each(data, function (key, val) {
        //if sendGroup.php returns true then alert success to the user
        if (val == "true") {
          alert("group has been added");
        } else {
          alert("error group has not been added")
        }
      })
        //refresh the groupList
        makeGroupList("");
    }
  });
});

// Making the search feature responsive and filter down names
document.getElementById('groupSearchBar').addEventListener('input', function (e) {
  //get the search value then run fill_dropdown_menu with this input
  const search_entry = e.target.value.toLowerCase();
  makeGroupList(search_entry)
});

//function to make the user list when the group is clicked
function makeUserList(group, search) {
  currentGroup = group;
  users = [];
  document.getElementById("userList").innerHTML = "";
  document.getElementById("userListHeading").innerHTML = group;
  //used to generate the user list from the group clicked
  $.ajax({
    dataType: "json",
    url: "groups_resources/getUsers.php",
    data: { groupName: group },
    success: function (data) {
      $.each(data, function (key, val) {
        //if the search is empty or the search input is in the word then add it to users
        if (val.includes(search) || search == "") {
          users.push(val);
        }
      });

      if (users.length == 0) {
        //display that no users are found
        document.getElementById("userList").innerHTML = "No users found";
      } else {
        document.getElementById("userList").innerHTML = users.map(generateList).join(' ');
        //takes in a item in an array and returns the code to put i as an item in a list
        function generateList(item) {
          //class = 'classUsers' is used to group all items in the user list so they can be found
          return "<li class='ClassUsers'>" + item + "</li>" + "<button class='removeUsers btn btn-sm btn-danger' id = "+item+" >Delete</button>";
        };
        document.getElementById("userList").style.display = "block"
        makeUsersClickable();
      }
    }
  });
}

//function makeUsersClickable is ran so that when a user is clicked they are removed from the group
function makeUsersClickable() {
  //find all the list items currently in the user list
  let usernameRemoveList = document.querySelectorAll(".removeUsers");
  //for each list item make it so when clicked it calls removeUser for that user
  for (let i = 0; i < usernameRemoveList.length; i++) {
    usernameRemoveList[i].addEventListener("click", () => {
      let usernameRemove = usernameRemoveList[i].id;
      let confirmMsg = "Are you sure you want to remove this user from the group";
      if (confirm(confirmMsg) == true) {
        removeUser(usernameRemove);
      }
    }
    )
  }
}


//removeUser function which takes in a username and removes them from the currently clicked group
function removeUser(user) {
  //ajax call to removeUsers.php passing currentGroup and user
  $.ajax({
    dataType: "json",
    url: "groups_resources/removeUsers.php",
    data: {
      groupName: currentGroup,
      username: user
    },
    success: function (data) {
      //if true then output success message
      if (data == "true") {
        makeUserList(currentGroup, "");
        alert(user + " has been removed from " + currentGroup);
      } else {
        alert("Failed to remove user");
      }
    }
  });

}

//finds the add group form by its ID
const userAddForm = document.getElementById("NewUserForm");
//adds an event listener for the add group submit event
userAddForm.addEventListener("submit", function (event) {
  event.preventDefault();
  //get the value from the form
  let AddNewUser = document.getElementById("usernameSelected").value;
  //ajax request to sendUsers.php passing AddNewUser and getting back true or false
  $.ajax({
    dataType: "json",
    url: "groups_resources/sendUsers.php",
    method: "get",
    data: {
      username: AddNewUser,
      groupName: currentGroup
    },
    success: function (data) {
      $.each(data, function (key, val) {
        //if sendUser.php returns true then alert success to the user
        if (val == "true") {
          //refresh the groupList and clear the dropdown menu
          makeUserList(currentGroup, "");
          document.getElementById('dropdownMenuButton').textContent = "Select Employee";
          document.getElementById('usernameSelected').value = "";
        } else {
          alert("error user has not been added")
        }
      })

    }
  });
});

// Making the search feature responsive and filter down names
document.getElementById('userSearchBar').addEventListener('input', function (e) {
  //get the search value then run fill_dropdown_menu with this input
  const search_entry = e.target.value.toLowerCase();
  makeUserList(currentGroup, search_entry)
});

function fill_dropdown_menu(search) {
  let users = [];
  //remove any options currently in the dropdown menu
  while (dropdown_menu_users.hasChildNodes()) {
    dropdown_menu_users.removeChild(dropdown_menu_users.firstChild);
  }
  //ajax request to getAllUsers.php passing which gets all users and adds them to the dropdown list
  $.ajax({
    dataType: "json",
    url: "groups_resources/getAllUsers.php",
    method: "get",
    success: function (users) {
      users.forEach(user => {
        if (user.includes(search) || search == "") {
          //for each user returned add them to the dropdown list if they are in the search or the search is empty
          const item = document.createElement('div');
          item.classList.add('dropdown-item');
          // item.type = 'button';
          item.textContent = user;
          item.setAttribute('data-id', user);
          dropdown_menu_users.appendChild(item);
        }
      });
    }
  });
};

// Adding an event listener so when a employee name is clicked it is selected
dropdown_menu_users.addEventListener('click', function (e) {
  if (e.target.matches('.dropdown-item')) {
    document.getElementById('dropdownMenuButton').textContent = e.target.textContent;
    document.getElementById('usernameSelected').value = e.target.getAttribute('data-id');
  }
});

// Making the search feature responsive and filter down names
document.getElementById('search_users').addEventListener('input', function (e) {
  //get the search value then run fill_dropdown_menu with this input
  const search_entry = e.target.value.toLowerCase();
  fill_dropdown_menu(search_entry);
});



document.getElementById("groupHeading").innerHTML = "All Groups"
//when the window loads set make the groupList and fill the dropdown menu
window.onload = function () {
  makeGroupList("");
  fill_dropdown_menu("");
}
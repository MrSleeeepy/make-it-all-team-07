//define variables to be used
let clickedTopic = "";
let clickedTopicID = -1;
let searchedTopics = "";
let clickedPost = "";
let clickedPostID = -1;
let clickedCommentModal = "";
let clickedPostTextModal = "";
let clickedPostModal = "";
let clickedTopicModal = "";
let editTextClicked = "";
let editTopicTextClicked = "";
let editPostTextClicked = "";
let editPostMessageTextClicked = "";
let manager = "false";
let checkPostsShow = "false";
//modal button position variables
let clickedTopicModalID = "";
let clickedPostModalID = "";
let clickedPostTextModalID = "";
let clickedCommentModalID = "";
let limitedVisibility = 0;
// Getting the drop down menus
const dropdown_menu_users = document.getElementById('dropdown_menu_viewers');
const dropdown_menu_users_posts = document.getElementById('dropdown_menu_user_viewers');
//finds the post search form by its ID
const postSearchForm = document.getElementById("newPostSearch");
//adds an event listener for the form submit event
postSearchForm.addEventListener("submit", function (event) {
  event.preventDefault();
  const postSearchInput = document.getElementById("postSearchBar").value;
  searchPost(postSearchInput);
});


//finds the add topic form by its ID
const topicAddForm = document.getElementById("NewTopicForm");
//adds an event listener for the add topic submit event
topicAddForm.addEventListener("submit", function (event) {
  event.preventDefault();
  //get the value from the form
  const AddNewTopic = document.getElementById("AddNewTopic").value;
  //ajax request to sendTopic.php passing AddNewTopic and getting back true or false
  $.ajax({
    dataType: "json",
    url: "knowledge_resources/sendTopic.php",
    method: "get",
    data: { topicName: AddNewTopic },
    success: function (data) {
      $.each(data, function (key, val) {
        //if sendTopic.php returns true then alert success to the user
        if (val == "true") {
          alert("topic has been added");
        } else {
          alert("error topic has not been added")
        }
      })
      //refresh topic list
      makeTopicList("");
      document.getElementById("AddNewTopic").value = "";
      clickedPost = "";
      clickedPostID = -1;
    }
  });
});


//finds the add post form by its ID
const postAddForm = document.getElementById("NewPostForm");
//adds an event listener for the add post submit event
postAddForm.addEventListener("submit", function (event) {
  event.preventDefault();
  const postAddInput = document.getElementById("AddNewPostTitle").value;
  const postTextAddInput = document.getElementById("AddNewPostText").value;
  addPost(postAddInput, postTextAddInput);
});

//finds the add comment form by its ID
const commentAddForm = document.getElementById("newCommentForm");
//adds an event listener for the add comment submit event
commentAddForm.addEventListener("submit", function (event) {
  event.preventDefault();
  const commentAddInput = document.getElementById("addNewCommentText").value;
  addComment(commentAddInput);
});

//function to make the topic list taking in the search input 
function makeTopicList(search) {
  //used to generate the topic list by calling an ajax function to getTopics.php
  $(document).ready(function () {
    topics = [[]]
    $.ajax({
      dataType: "json",
      url: "knowledge_resources/getTopics.php",
      success: function (data) {
        $.each(data, function (key, val) {
          //check the data is not undefined then check if its in the search or if the search is blank
          if (typeof(val[0]) != "undefined" && typeof(val[1]) != "undefined"){
            if (val[0].toLowerCase().includes(search) || search == "") {
              topics.push(val);
            }
          }
        });

        //if there are no topics then display no topics found message (set to 1 as the query always returns an undefined object in position 1)
        if (topics.length == 1) {
          document.getElementById("Topic-List").innerHTML = "No topics found";
        } else {
          //generate a list of topics
          document.getElementById("Topic-List").innerHTML = topics.map(generateList).join(' ');
          //takes in a item in an array and returns the code to put it as an item in a list
          function generateList(item) {
            //checks if the item is not undefined
            if (typeof(item[0]) != "undefined" && typeof(item[1]) != "undefined"){
              //check if the user is logged in as a manager as if so we have an edit button
              if (manager == "true"){
                if (item[1] == clickedTopicID){
                  //if the item[1] is the clicked topic then delete it
                  return "<li> <p class='ClassTopics' id=TPtext-"+item[1]+" style='display:inline-block; text-decoration: underline !important;\
                  text-underline-offset: 2px !important;\
                  text-decoration-thickness: 3px !important;\
                  text-decoration-color: #c6aa34 !important;'>" + item[0] + "</p> <button class='topicModalButton btn btn-sm btn-primary' id=TP-"+item[1]+" >Edit</button>"+"</li>";
                } else{
                //class = 'classTopics' is used to group all items in the topic list so they can be found by the setListPositions function
                return "<li> <p class='ClassTopics' id=TPtext-"+item[1]+" style='display:inline-block'>" + item[0] + "</p> <button class='topicModalButton btn btn-sm btn-primary' id=TP-"+item[1]+" >Edit</button>"+"</li>";
                }
              } else{
                //if the item[1] is the clicked topic then delete it
                if (item[1] == clickedTopicID){
                  return "<li> <p class='ClassTopics' id=TPtext-"+item[1]+" style='display:inline-block; text-decoration: underline !important;\
                  text-underline-offset: 2px !important;\
                  text-decoration-thickness: 3px !important;\
                  text-decoration-color: #c6aa34 !important;'>" + item[0] + "</p> </li>";
                } else{
                  return "<li> <p class='ClassTopics' id=TPtext-"+item[1]+" style='display:inline-block'>" + item[0] + "</p> </li>";
                }
              }
            }
          };
          //call functions to make buttons relating to topics clickable. Modal for editing topics is only shown and clickable for managers knowledgeAdmins 
          makeTopicsClickable();
          if (manager == "true"){
            makeTopicModalClickable();
          }
        };
      }
    });
  });
}

//cycles though all topic modal buttons and adds onclick
const makeTopicModalClickable = () => {
  let ListItems = document.querySelectorAll(".topicModalButton");
  for (let i = 0; i < ListItems.length; i++) {
    ListItems[i].addEventListener("click", () => {
      clickedTopicModal = i;
      clickedTopicModalID = ListItems[i].id.slice(3);
      //trigger modal opening
      $('#topicModal').modal('show');
    })
  }
}

//function to remove a topic from the database then reset the display for if the clicked topic is the deleted topic
function removeTopic() {
  $.ajax({
    url: "knowledge_resources/deleteTopic.php",
    data: {topic: clickedTopicModalID},
    success: function (data) {
      //if true then output success message
      if (data == "false") {
        alert("Failed to remove topic");
      } else {
        alert("Topic has been removed");
      }
      makeTopicList("");
      clickedPost = "";
      clickedPostID = -1;
      returnFromPost();
      makePostList("");
      document.getElementById("postListHeading").innerHTML = "Select a topic to display posts";
    }
  });
}



//function to edit a post from the database
function editTopic() {
  editTopicTextClicked = document.getElementById("editTopicText").value;
  if (editTopicTextClicked != ""){
    $.ajax({
      url: "knowledge_resources/editTopic.php",
      data: {topic: clickedTopicModalID, editText: editTopicTextClicked},
      success: function (data) {
        //if true then output success message
        if (data == "false") {
          alert("Failed to edit topic");
        } else {
          alert("Topic has been edited");
        }
        makeTopicList("");
        document.getElementById("editTopicText").value = "";
        if (checkPostsShow == "true") {
          document.getElementById("postListHeading").innerHTML = editTopicTextClicked;
        }
      }
    });
  }else {
    alert("Topic name can't be empty")
  }
}

// Making the search feature responsive and filter down names
document.getElementById('topicSearchBar').addEventListener('input', function (e) {
  //get the search value then run makeTopicList with this input
  const search_entry = e.target.value.toLowerCase();
  makeTopicList(search_entry)
});


//loops through all of the items in the topic list and adds an on click function to alert their position in the array
const makeTopicsClickable = () => {
  let topicListItems = document.querySelectorAll(".ClassTopics");
  for (let i = 0; i < topicListItems.length; i++) {
    topicListItems[i].addEventListener("click", () => {
      clickedTopic = topicListItems[i].innerHTML;
      clickedTopicID = topicListItems[i].id.slice(7);
      checkPostsShow = "true";
      makeTopicList("");
      clickedPost = "";
      clickedPostID = -1;
      makePostList("");
      //hide and show elements as a topic has been clicked
      document.getElementById("postMessage").style.display = "none";
      document.getElementById("newPostSearch").style.display = "block";
      document.getElementById("postReturnButton").style.display = "none";
      document.getElementById("newCommentForm").style.display = "none";
    }
    )
  }
}

//function to loop through all items in the currently displayed post list and add on-click functionality
function makePostsClickable() {
  let postListItems = document.querySelectorAll(".ClassPosts");
  for (let i = 0; i < postListItems.length; i++) {
      postListItems[i].addEventListener("click", () => {
      checkPostsShow = "false";
      //get the post id from the id of the button
      clickedPost = postListItems[i].innerHTML;
      clickedPostID = postListItems[i].id.slice(7);
      document.getElementById("postListHeading").innerHTML = clickedPost;

      makePostMessage(postListItems[i].innerHTML);
      makeCommentList(postListItems[i].innerHTML);
      //when a post is clicked the elements to be shown need to be changed
      document.getElementById("postMessage").style.display = "block";
      document.getElementById("postList").style.display = "block";

      document.getElementById("newPostSearch").style.display = "none";
      document.getElementById("postReturnButton").style.display = "block";
      document.getElementById("NewPostForm").style.display = "none";
      document.getElementById("newCommentForm").style.display = "block";
    });
  }
}


//function to make a list of posts given the topic selected
function makePostList(search) {
  //array to store posts from database
  posts = [[]];
  document.getElementById("postListHeading").innerHTML = clickedTopic;
  $.ajax({
    dataType: "json",
    url: "knowledge_resources/getPosts.php",
    method: "get",
    data: { topicID: clickedTopicID},
    success: function (data) {
      $.each(data, function (key, val) {
        //check the data is not undefined then check if its in the search or if the search is blank
        if (typeof(val[0]) != "undefined" && typeof(val[1]) != "undefined"){
        if (val[0].toLowerCase().includes(search) || search == "") {
          posts.push(val);
        }
      }
      });
       //if there are no posts then display no posts found message (set to 1 as the query always returns an undefined object in position 1)
      if (posts.length == 1) {
        document.getElementById("postList").innerHTML = "No posts found";
      } else {
        document.getElementById("postList").innerHTML = posts.map(generateList).join(' ');
        //takes in a item in an array and returns the code to put it as an item in a list
        function generateList(item) {
          if (typeof(item[0]) != "undefined" && typeof(item[1]) != "undefined"){
            if (manager == "true"){
              //class = 'classPosts' is used to group all items in the group list so they can be found
              return "<li> <p class='ClassPosts' id=POtext-" + item[1] + " style='display:inline-block'>" + item[0] + "</p> <button class='postModalButton btn btn-sm btn-primary' id=PO-"+item[1]+" >Edit</button>"+"</li>";
            }else{
              return "<li> <p class='ClassPosts' id=POtext-" + item[1] + " style='display:inline-block'>" + item[0] + "</p> </li>";
            }            
          }
        };
        //call function to make the posts clickable and the edit modal clickable for managers/knowledgeAdmins 
        makePostsClickable(posts);
        if (manager == "true"){
          makePostModalClickable();
        }
      }
      //update the display
      document.getElementById("postList").style.display = "block";
      document.getElementById("postMessage").style.display = "none";
      document.getElementById("NewPostForm").style.display = "block";
    }
  });
}

//cycles though all post modal buttons and adds onclick
const makePostModalClickable = () => {
  let ListItems = document.querySelectorAll(".postModalButton");
  for (let i = 0; i < ListItems.length; i++) {
    ListItems[i].addEventListener("click", () => {
      clickedPostModal = i;
      clickedPostModalID = ListItems[i].id.slice(3);
      //trigger modal opening
      $('#postModal').modal('show');
    })
  }
}

//function to remove a post from the database
function removePost() {
  $.ajax({
    url: "knowledge_resources/deletePost.php",
    data: {post: clickedPostModalID},
    success: function (data) {
      //if true then output success message
      if (data == "false") {
        alert("Failed to remove post");
      } else {
        alert("Post has been removed");
      }
      //remake the postList
      makePostList("");
    }
  });
}

//function to edit a post from the database
function editPost() {
  editPostTextClicked = document.getElementById("editPostNameText").value;
  if (editPostTextClicked != ""){
  $.ajax({
    url: "knowledge_resources/editPost.php",
    data: {post: clickedPostModalID, editText: editPostTextClicked},
    success: function (data) {
      //if true then output success message
      if (data == "false") {
        alert("Failed to edit post");
      } else {
        alert("Post has been edited");
      }
      //remake the post list
      makePostList("");
      document.getElementById("editPostNameText").value = "";
    }
  });
  }else {
    alert("Post name can't be empty");
  }
}

//function to make a list of comments given the post selected
function makeCommentList() {
  //array to store positions of relevant comments
  comments = [[]];
  count = 0;
  $.ajax({
    dataType: "json",
    url: "knowledge_resources/getComments.php",
    method: "get",
    data: { clickedPostID: clickedPostID},
    success: function (data) {
      $.each(data, function (key,val) {
        //check the return is not undefined
        if (typeof(val[0]) != "undefined" && typeof(val[1]) != "undefined"){
        comments.push(val);
        }
      });
      //check if comments is empty (set to 1 as array has an undefined element)
      if (comments.length == 1) {
        document.getElementById("postList").innerHTML = "No comments";
      } else {
        document.getElementById("postList").innerHTML = comments.map(generateList).join(' ');
        //takes in a item in an array and returns the code to put it as an item in a list
        function generateList(item) {
          //check if comments is empty (set to 1 as array has an undefined element)
          if (typeof(item[0]) != "undefined" && typeof(item[1]) != "undefined"){
            //update commentText and ID with the retrieved values
            commentText = item[0];
            commentID = item[1];
            //if manager then have an edit button
            if (manager == "true"){
              //class = 'classComments' is used to group all items in the group list so they can be found by the setListPositions function
              return "<li> <p class='ClassComments' style='display:inline-block'>" + commentText + "</p> <button class='commentModalButton btn btn-sm btn-primary' id=cm-"+commentID+">Edit</button>"+"</li>";
            }else{
              return "<li> <p class='ClassComments' style='display:inline-block'>" + commentText + "</p> </li>";
            }
          }
        };

      }
      //if manager then make the edit button clickable
      if (manager == "true"){
        makeCommentModalClickable();
      }

      document.getElementById("postList").style.display = "block";

    }
  });
}

//cycles though all comment edit modal buttons and adds onclick
const makeCommentModalClickable = () => {
  let ListItems = document.querySelectorAll(".commentModalButton");
  for (let i = 0; i < ListItems.length; i++) {
    ListItems[i].addEventListener("click", () => {
      clickedCommentModal = i;
      clickedCommentModalID = ListItems[i].id.slice(3);
      //trigger modal opening
      $('#commentModal').modal('show');
    })
  }
}



//function to remove a comment from the database
function removeComment() {
  $.ajax({
    url: "knowledge_resources/deleteComment.php",
    data: {comment: clickedCommentModalID},
    success: function (data) {
      //if true then output success message
      if (data == "false") {
        alert("Failed to remove comment");
      } else {
        alert("Comment has been removed");
      }
      makeCommentList(clickedPost);
    }
  });
}

//function to edit a comment from the database
function editComment() {
  editTextClicked = document.getElementById("editCommentText").value;
  if (editTextClicked != ""){
    $.ajax({
      url: "knowledge_resources/editComment.php",
      data: {comment: clickedCommentModalID, editText: editTextClicked},
      success: function (data) {
        //if true then output success message
        if (data == "false") {
          alert("Failed to edit comment");
        } else {
          alert("Comment has been edited");
        }
        makeCommentList(clickedPost);
        document.getElementById("editCommentText").value = "";
      }
    });
  }else {
    alert("Comment can't be empty")
  }
}

//function to get the post message description given the post selected
function makePostMessage() {
  //array to store positions of relevant comments
  message = [[]];
  count = 0;
  $.ajax({
    dataType: "json",
    url: "knowledge_resources/getPostMessage.php",
    method: "get",
    data: { clickedPostID: clickedPostID},
    success: function (data) {
      $.each(data, function (key, val) {
        //check the data is not undefined
        if (typeof(val[0]) != "undefined" && typeof(val[1]) != "undefined"){
          message.push(val);
        }
      });
      if (message.length == 0) {
        document.getElementById("postMessage").innerHTML = "No post message";
      } else {
        document.getElementById("postMessage").innerHTML = message.map(generateList).join(' ');
        //takes in a item in an array and returns the code to put it as an item in a list
        function generateList(item) {
          if (typeof(item[0]) != "undefined" && typeof(item[1]) != "undefined"){
            postMessageText = item[0];
            postMessageTextID = item[1];
            //if manager is true then have an edit button
            if (manager == "true"){
              return "<li> <p class='ClassPostMessage' style='display:inline-block'>" + postMessageText + "</p> <button class='postTextModalButton btn btn-sm btn-primary' id=pm-"+postMessageTextID+">Edit Post</button>"+"</li>";
            }else{
              return "<li> <p class='ClassPostMessage' style='display:inline-block'>" + postMessageText + "</p> </li>";
            }
          }
        };
      }
      //make the edit button clickable for managers/knowledgeAdmins
      if (manager == "true"){
        makePostTextModalClickable();
      }
    }
  });
}

//cycles though all post text modal buttons and adds onclick
const makePostTextModalClickable = () => {
  let ListItems = document.querySelectorAll(".postTextModalButton");
  for (let i = 0; i < ListItems.length; i++) {
    ListItems[i].addEventListener("click", () => {
      clickedPostTextModal = i;
      clickedPostTextModalID = ListItems[i].id.slice(3);
      //trigger modal opening
      $('#postTextModal').modal('show');
    })
  }
}

//function to edit a post message from the database
function editPostMessage() {
  editPostMessageTextClicked = document.getElementById("editPostText").value;
  //check the entry is not empty
  if (editPostMessageTextClicked != ""){
  $.ajax({
    url: "knowledge_resources/editPostMessage.php",
    data: {postMessage: clickedPostTextModalID, editText: editPostMessageTextClicked},
    success: function (data) {
      //if true then output success message
      if (data == "false") {
        alert("Failed to edit post message");
      } else {
        alert("Post message has been edited");
      }
      makePostMessage();
      document.getElementById("editPostText").value = "";
    }
  });
}else {
  alert("Post message can't be empty")
}
}

// Making the search feature responsive and filter down names
document.getElementById('postSearchBar').addEventListener('input', function (e) {
  //get the search value then run makePostList with this input
  const search_entry = e.target.value.toLowerCase();
  makePostList(search_entry);
});

//add a return function for the return button to change the clicked post to -1 to then display the postlist
document.querySelector("#postReturnButton").addEventListener("click", returnFromPost);
function returnFromPost() {
  checkPostsShow = "true";
  document.getElementById("postListHeading").innerHTML = clickedTopic;
  makePostList("");
  document.getElementById("postReturnButton").style.display = "none";
  document.getElementById("NewPostForm").style.display = "block";
  document.querySelector("#postSearchBar").value = "";
  document.getElementById("newPostSearch").style.display = "block";
  document.getElementById("postMessage").style.display = "none";
  document.getElementById("newCommentForm").style.display = "none";
  clickedPost = "";
  clickedPostID = -1;
}

//function to add new post
function addPost(postName, postText) {
  //checks if the input is not empty
  if (postName.trim() !== "" && postText.trim() !== "") {
    //ajax request to sendPost.php which attempts to add a new post which has unlimited visibility within the topic 
    $.ajax({
      dataType: "json",
      url: "knowledge_resources/sendPost.php",
      method: "get",
      data: {
        postName: postName,
        postText: postText,
        currentTopic: clickedTopicID
      },
      success: function (data) {
        $.each(data, function (key, val) {
          //if sendPost.php returns true then alert success to the user
          if (val == "true") {
            alert("Post has been added");
            document.getElementById("AddNewPostTitle").value = "";
            document.getElementById("AddNewPostText").value = "";
          } else {
            alert("error post has not been added")
          }
        })
        //refres the postlist
        makePostList("");
      }
    });
  } else {
    alert("Post title and text cannot be empty")
  }
}

//function to add new comment
function addComment(commentText) {
  //checks if the input is not empty
  if (commentText.trim() !== "") {
    //ajax request to sendComment.php which attempts to add a new comment
    $.ajax({
      dataType: "json",
      url: "knowledge_resources/sendComment.php",
      method: "get",
      data: {
        commentText: commentText,
        currentPost: clickedPostID
      },
      success: function (data) {
        $.each(data, function (key, val) {
          //if sendComment.php returns true then alert success to the user
          if (val == "true") {
            alert("Comment has been added");
            document.getElementById("addNewCommentText").value = "";
          } else {
            alert("error comment has not been added");
          }
        })
        //refresh the commentList
        makeCommentList(clickedPostID);
      }
    });
  } else {
    alert("Comment text cannot be empty");
  }
}

//event listener for when viewerModal is opened
const viewerModalOpen = document.getElementById("manageViewers")
viewerModalOpen.onclick = checkVisibility;

//fillviewer modal which checks the whether the modal is being filled with data about a post or topic and if it has limited visibility or not
function fillViewerModal() {
  document.getElementById("limitedVisibilityCheckbox").checked = true;
  if (clickedPost != "") { 
    if (limitedVisibility == false){ 
      //a post is clicked and it has unlimited visibility so hide the elements relating to viewers 
      document.getElementById("viewerLeftColumn").style.display = 'none';
      document.getElementById("viewerRightColumn").style.display = 'none';
      document.getElementById("NewViewerForm").style.display = 'none';
      document.getElementById("NewViewerUserForm").style.display = 'none';
      document.getElementById("limitedVisibilityCheckbox").checked = false;
    } else {
      //a post is clicked and it has limited visibility so show the elements relating to viewers and call functions to get the viewer lists 
      document.getElementById("viewerLeftColumn").style.display = 'block';
      document.getElementById("viewerRightColumn").style.display = 'block';
      document.getElementById("NewViewerForm").style.display = 'block';
      document.getElementById("NewViewerUserForm").style.display = 'block';
      document.getElementById("viewerRightColumn").style.visibility = 'visible';
      document.getElementById("newViewerUserDiv").style.visibility = 'visible';
      document.getElementById("limitedVisibilityCheckbox").checked = true;
      fillViewerModalPostGroups();
      fillViewerModalPostUsers();
      fillViewerModalPostDropdown("");
    }
  } else {
    //no post clicked and the topic has unlimited visibility so hide the elements relating to viewers
    if (limitedVisibility == false){  
      document.getElementById("viewerLeftColumn").style.display = 'none';
      document.getElementById("viewerRightColumn").style.display = 'none';
      document.getElementById("NewViewerForm").style.display = 'none';
      document.getElementById("NewViewerUserForm").style.display = 'none';
      document.getElementById("limitedVisibilityCheckbox").checked = false;
    } else {
    //no post clicked and the topic has limited visibility so show the elements relating to viewers and get the data
      document.getElementById("viewerLeftColumn").style.display = 'block';
      document.getElementById("viewerRightColumn").style.display = 'block';
      document.getElementById("NewViewerForm").style.display = 'block';
      document.getElementById("NewViewerUserForm").style.display = 'block';
      document.getElementById("viewerRightColumn").style.visibility = 'hidden';
      document.getElementById("newViewerUserDiv").style.visibility = 'hidden';
      document.getElementById("limitedVisibilityCheckbox").checked = true;
      fillViewerModalTopicGroups();
    }
  }
  //fill the dropdowns
  fillViewerModalTopicDropdown("");
}

//function that determines whether a post is clicked or a topic and then gets whether or not it is visible
function checkVisibility(){
  limitedVisibility = false;
  $.ajax({
    dataType: "json",
    url: "knowledge_resources/getLimitedVisibility.php",
    data: { postID: clickedPostID,
            topicID: clickedTopicID },
    success: function (data) {
      $.each(data, function (key, val) {
        if (val == 1){
          limitedVisibility = true
        }
      });
      fillViewerModal();
    }
  });
}

//add an event listener for changing whether a topic/post has limited visibility
document.getElementById("limitedVisibilityCheckbox").addEventListener("click",function(){
  //if limitedVisibility is true then the click sets it to unlimited and then the same the other way round
  if (limitedVisibility == true){
    setUnlimitedVisibility();
    limitedVisibility = false;
  } else{
    setLimitedVisibility();
    limitedVisibility = true;
  }
})

//set unlimited visibility for the clicked post if its been clicked or the clicked topic otherwise
function setUnlimitedVisibility(){
  $.ajax({
    dataType: "json",
    url: "knowledge_resources/setUnlimitedVisibility.php",
    data: { postID: clickedPostID,
            topicID: clickedTopicID },
    success: function (data) {
      alert("set to unlimited visibility");
      fillViewerModal();
    }
  });
};

//set limited visibility for the clicked post if its been clicked or the clicked topic otherwise
function setLimitedVisibility(){
  $.ajax({
    dataType: "json",
    url: "knowledge_resources/setLimitedVisibility.php",
    data: { postID: clickedPostID,
            topicID: clickedTopicID },
    success: function (data) {
      alert("set to limited visibility");
      fillViewerModal();
    }
  });
};


//fill the viewer modal with the relevent post groups
function fillViewerModalPostGroups() {
  //post clicked so fill viewer group list based on the post
  groups = [[]]
  $.ajax({
    dataType: "json",
    url: "knowledge_resources/getPostViewerGroups.php",
    data: { postID: clickedPostID },
    success: function (data) {
      $.each(data, function (key, val) {
        //check the data is not undefined
        if (typeof(val[0]) != "undefined" && typeof(val[1]) != "undefined"){
          groups.push(val);
        }
      });

      //check for if there is no data. Set to one as the array gets an undefined object in slot 1
      if (groups.length == 1) {
        document.getElementById("viewerModalGroupList").innerHTML = "No viewer groups found";
      } else {
        document.getElementById("viewerModalGroupList").innerHTML = groups.map(generateList).join(' ');
        //takes in a item in an array and returns the code to put it as an item in a list
        function generateList(item) {
          //check if the data is not undefined then add the viewer group to the list
          if (typeof(item[0]) != "undefined" && typeof(item[1]) != "undefined"){
          return "<li class='ClassViewerModalGroups'>" + item[2] +  "<button class='removePostGroup btn btn-sm btn-danger' id = PVG-"+item[0]+"-"+item[1]+" style='margin-left:5px; margin-bottom:1px;'>Delete</button></li>";
          }
        };
        //make the delete buttons clickable
        makeDeletePostGroupsViewersClickable();
      };
    }
  });
}

//loops through all of the items in post group viewer list and makes them clickable
function makeDeletePostGroupsViewersClickable() {
  let postGroupDeleteListItems = document.querySelectorAll(".removePostGroup");
  for (let i = 0; i < postGroupDeleteListItems.length; i++) {
    postGroupDeleteListItems[i].addEventListener("click", () => {
      //get the postID and groupID from the buttons ID
      let postGroupNameDelete = postGroupDeleteListItems[i].id.slice(4);
      postID = postGroupNameDelete.split("-")[0];
      groupID = postGroupNameDelete.split("-")[1];
      //call deletePostGroupViewer on the group and post
      $.ajax({
        dataType: "json",
        url: "knowledge_resources/deletePostGroupsViewer.php",
        data: {
          groupID: groupID,
          postID: postID,
        },
        success: function (data) {
            if (data== "true"){
              alert("group has been removed");
            } else{
              alert("error group has not been removed");
            }
          //reload the list th show the group has been removed
          fillViewerModalPostGroups()
        }
      });
    
    }
    )
  }
}

//fill viewer modal with the post users
function fillViewerModalPostUsers() {
  document.getElementById("viewerModalUserList").innerHTML = "";
  users = []
  //call getPostViewerUsers to get the users who can view a post
  $.ajax({
    dataType: "json",
    url: "knowledge_resources/getPostViewerUsers.php",
    data: { postID: clickedPostID },
    success: function (data) {
      $.each(data, function (key, val) {
        users.push(val);
        console.log(val);
      });
      //check if the list is empty
      if (users.length == 0) {
        document.getElementById("viewerModalUserList").innerHTML = "No viewer users found";
      } else {
        document.getElementById("viewerModalUserList").innerHTML = users.map(generateList).join(' ');
        //takes in a item in an array and returns the code to put it as an item in a list
        function generateList(item) {
          return "<li class='ClassViewerModalUsers'>" + item + "<button class='removePostUsers btn btn-sm btn-danger' id = "+item+" style='margin-left:5px;margin-bottom:1px;'>Delete</button></li>";
        };
        //make the delete buttons clickable
        makeDeletePostUsersViewersClickable();
      };
    }
  });
}

//loops through all of the delete buttons and makes them clickable
function makeDeletePostUsersViewersClickable() {
  let postUserDeleteListItems = document.querySelectorAll(".removePostUsers");
  for (let i = 0; i < postUserDeleteListItems.length; i++) {
    postUserDeleteListItems[i].addEventListener("click", () => {
      let postUsernameDelete = postUserDeleteListItems[i].id;
      //call to delete post users viewer to remove them from viewing the post
      $.ajax({
        dataType: "json",
        url: "knowledge_resources/deletePostUsersViewer.php",
        data: {
          username: postUsernameDelete,
          postName: clickedPost,
          topicName: clickedTopic
        },
        success: function (data) {
            if (data== "true"){
              alert("group has been removed");
            } else{
              alert("error group has not been removed");
            }
          //refresh the list
          fillViewerModalPostUsers()
        }
      });
    
    }
    )
  }
}

//function to fill the viewer modals post dropdown and make it searchable
function fillViewerModalPostDropdown(search) {
  //remove any options currently in the dropdown menu
  while (dropdown_menu_users_posts.hasChildNodes()) {
    dropdown_menu_users_posts.removeChild(dropdown_menu_users_posts.firstChild);
  }
  //ajax request to getViewerUserPosts to get users for the dropdown
  $.ajax({
    dataType: "json",
    //use getViewerUserPosts.php to get the users who could potentially be added to a post 
    url: "knowledge_resources/getViewerUserPosts.php",
    method: "get",
    data :{
      topicName : clickedTopic,
    },
    success: function (users) {
      //check if the user is in the search or if the search is empty
      users.forEach(user=> {
        if (user.includes(search) || search == "") {
          //for each user returned add them to the dropdown list
          const item = document.createElement('div');
          item.classList.add('dropdown-item');
          item.textContent = user;
          item.setAttribute('data-id', user);
          dropdown_menu_users_posts.appendChild(item);
        }
      });
    }
  });
};

// Adding an event listener so when a employee name is clicked it is selected
dropdown_menu_users_posts.addEventListener('click', function (e) {
  if (e.target.matches('.dropdown-item')) {
    document.getElementById('dropdownUserMenuButton').textContent = e.target.textContent;
    document.getElementById('userViewerSelected').value = e.target.getAttribute('data-id');
  }
});

// Making the search feature responsive and filter down names
document.getElementById('searchAddUserViewer').addEventListener('input', function (e) {
  const search_entry = e.target.value.toLowerCase();
  fillViewerModalPostDropdown(search_entry);
});

//finds the add user post form by its ID to make it so a user can view a post
const userPostAddForm = document.getElementById("NewViewerUserForm");
//adds an event listener for the add group submit event
userPostAddForm.addEventListener("submit", function (event) {
  event.preventDefault();
  //get the value from the form
  let AddNewViewer = document.getElementById("userViewerSelected").value;
  //ajax request to sendUserViewer to add them
  $.ajax({
    dataType: "json",
    url: "knowledge_resources/sendUserViewer.php",
    method: "get",
    data: {
      newUser: AddNewViewer,
      topicName: clickedTopic,
      postID : clickedPostID
    },
    success: function (data) {
      $.each(data, function (key, val) {
        //if sendGroup.php returns true then alert success to the user
        if (val == "true") {
          //refresh the groupList and clear the dropdown menu
          fillViewerModalPostUsers();
          document.getElementById('dropdownUserMenuButton').textContent = "Select viewer to add";
          document.getElementById('userViewerSelected').value = "";
        } else {
          alert("error viewer has not been added")
        }
      })
    }
  });
});


function fillViewerModalTopicGroups() {
  //no post clicked so fill viewer group list based on the topic
  groups = [[]]
  $.ajax({
    dataType: "json",
    url: "knowledge_resources/getTopicViewerGroups.php",
    data: { topicID: clickedTopicID },
    success: function (data) {
      $.each(data, function (key, val) {
        //check the data is not undefined
        if (typeof(val[0]) != "undefined" && typeof(val[1]) != "undefined"){
          groups.push(val);
        }
      });
      //check if groups is empty as it will have an undefined object in postion 1
      if (groups.length == 1) {
        document.getElementById("viewerModalGroupList").innerHTML = "No viewer groups found";
      } else {
        document.getElementById("viewerModalGroupList").innerHTML = groups.map(generateList).join(' ');
        //takes in a item in an array and returns the code to put it as an item in a list
        function generateList(item) {
          //check the item is not undefined
          if (typeof(item[0]) != "undefined" && typeof(item[1]) != "undefined"){
            return "<li class='ClassViewerModalGroups'>" + item[2] + "<button class='removeTopicGroup btn btn-sm btn-danger' id = TVG-"+item[0]+"-"+item[1]+" style='margin-left:5px; margin-bottom:1px;'>Delete</button></li>";
          }
        };
      };
      //make the modal topic group remove buttons clickable
      makeModalTopicGroupsClickable();
    }
  });
}

//loops through all of the delete topic group buttons and makes them clickable
const makeModalTopicGroupsClickable = () => {
  let ListItems = document.querySelectorAll(".removeTopicGroup");
  for (let i = 0; i < ListItems.length; i++) {
  ListItems[i].addEventListener("click", () => {
    //get the topicID and groupID via slicing and splitting the item
    let topicGroupNameDelete = ListItems[i].id.slice(4);
    topicID = topicGroupNameDelete.split("-")[0];
    groupID = topicGroupNameDelete.split("-")[1];
    $.ajax({
      dataType: "json",
      url: "knowledge_resources/deleteTopicGroupsViewer.php",
      data: {
        groupID: groupID,
        topicID: topicID,
      },
      success: function (data) {
          if (data== "true"){
            alert("group has been removed");
          } else{
            alert("error group has not been removed");
          }
        //run fillViewerModalTopicGroups
        fillViewerModalTopicGroups();
      }
    });
    }
    )
  }
}



function fillViewerModalTopicDropdown(search) {
  //remove any options currently in the dropdown menu
  while (dropdown_menu_users.hasChildNodes()) {
    dropdown_menu_users.removeChild(dropdown_menu_users.firstChild);
  }
  //ajax request to getViewerGroups.php which will get the groups that can view a post
  $.ajax({
    dataType: "json",
    //reuse getGroups.php from groups resources 
    url: "knowledge_resources/getViewerGroups.php",
    method: "get",
    data :{
      postName : clickedPost,
      topicName : clickedTopic
    },
    success: function (groups) {
      groups.forEach(group => {
        if (group.includes(search) || search == "") {
          //for each group returned add them to the dropdown list
          const item = document.createElement('div');
          item.classList.add('dropdown-item');
          // item.type = 'button';
          item.textContent = group;
          item.setAttribute('data-id', group);
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
    document.getElementById('viewerSelected').value = e.target.getAttribute('data-id');
  }
});

// Making the search feature responsive and filter down names
document.getElementById('searchAddViewer').addEventListener('input', function (e) {
  //get the search value then run fill_dropdown_menu with this input
  const search_entry = e.target.value.toLowerCase();
  fillViewerModalTopicDropdown(search_entry);
});

//finds the add group form by its ID
const userAddForm = document.getElementById("NewViewerForm");
//adds an event listener for the add group submit event
userAddForm.addEventListener("submit", function (event) {
  event.preventDefault();
  //get the value from the form
  let AddNewViewer = document.getElementById("viewerSelected").value;
  //ajax request to sendGroup.php passing AddNewViewer and getting back true or false
  $.ajax({
    dataType: "json",
    url: "knowledge_resources/sendGroup.php",
    method: "get",
    data: {
      groupName: AddNewViewer,
      topicName: clickedTopic,
      postName : clickedPost
    },
    success: function (data) {
      $.each(data, function (key, val) {
        //if sendGroup.php returns true then alert success to the user
        if (val == "true") {
          //refresh the groupList and clear the dropdown menu
          if (clickedPost == ""){
            fillViewerModalTopicGroups();
          } else{
            fillViewerModalPostGroups();
          }
          document.getElementById('dropdownMenuButton').textContent = "Select viewer to add";
          document.getElementById('viewerSelected').value = "";
        } else {
          alert("error viewer has not been added")
        }
      })

    }
  });
});

//set the heading
document.getElementById("topicHeading").innerHTML = "All Topics"
//on page load set the display properties, run makeTopicList and get whether the user is a manager
window.onload = function () {
  makeTopicList("");
  clickedPost = "";
  clickedPostID = -1;
  document.getElementById("postReturnButton").style.display = "none"
  document.getElementById("NewPostForm").style.display = "none"
  document.getElementById("newPostSearch").style.display = "none";
  document.getElementById("postMessage").style.display = "none";
  document.getElementById("newCommentForm").style.display = "none";

  $.ajax({
    dataType: "json",
    url: "knowledge_resources/checkManager.php",
    method: "get",
    success: function (data) {
        //if checkManager.php returns true then set manager is true
        if (data == true) {
          document.getElementById("groupsBtn").style.visibility = "visible";
          document.getElementById("manageViewers").style.visibility = "visible";
          manager = "true";
        }
    }
  });
}


<template>
    <div class="align-self-center" @click="HideDetails">
        <div class="input-group col-sm-5 searchList" style="display:none;"
             :class="((list_selected.type === 'board') ? 'searchList-board' : 'searchList')">
            <div class="input-group">
                <input class="form-control searchTaskList"
                       type="text" id="myInput"
                       placeholder="Search for names.."
                       title="Type in a name"
                       autocomplete="off"
                       @keyup="searchDataFormTask($event)">

                <div class="input-group-append">
                    <select class="form-control " v-model="search_type" @change="searchBYType"
                            style="width: auto;z-index: 999;border-radius: 0px 5px 5px 0px;height: 38px;">
                        <option value="all"> All</option>
                        <option value="this"> This {{list.type}}</option>
                    </select>
                </div>
            </div>
            <ul class="myUL" id="myUL">
                <template v-for="task in searchData.tasks" v-if="searchData.tasks.length > 0">
                    <li>
                        <a @mouseover="selectTaskFromTaskTreeList(task)" style="word-break: break-all"
                           @click="showSingleView(task)"
                           href="Javascript:void(0)">
                            {{task.title}}
                        </a>
                    </li>
                </template>
                <template v-else>
                    <li>
                        <a href="javascript:void(0)">
                            No task found!
                        </a>
                    </li>
                </template>

            </ul>
            <ul class="myUL-user-hide" id="myUL-user">
                <template v-for="user in searchData.users"
                          v-if="searchData.users.length > 0 && searchData.users !== undefined">
                    <li @click="SearchTaskByAssignedUser(user.id,user.name)">
                        <a href="javascript:void(0)">
                                <span class="assignUser-suggest-photo">
                                        {{(user.name !== null) ? user.name.substring(0,2) : ''}}
                                </span>
                            {{user.name}}
                        </a>
                    </li>
                </template>
                <template v-else>
                    <li>
                        <a href="javascript:void(0)">
                            No user found!
                        </a>
                    </li>
                </template>
            </ul>
        </div>
        <nav class="navbar-expand-md navbar-spark">
            <div class="container-fluid">
                <div class="collapse navbar-collapse show">
                    <div class="nav-train-station">
                        <div class="nav-train">
                            <ul class="navbar-nav navbar-nav-cabin ml-4 float-sm-left">
                                <li class="nav-item dropdown" @click="setOverview">
                                    <a aria-expanded="false" aria-haspopup="true"
                                       class="d-block d-md-flex text-center nav-link " href="#">
                                        <span class="d-none d-md-block">Overview</span>
                                    </a>
                                </li>
                            </ul>
                            <template v-for="nav in AllNavItems">
                                <ul class="navbar-nav navbar-nav-cabin ml-4 float-sm-left">
                                    <li class="nav-item dropdown">
                                        <a aria-expanded="false" aria-haspopup="true"
                                           class="d-block d-md-flex text-center nav-link dropdown-toggle"
                                           data-toggle="dropdown" href="#">
                                            <span class="d-none d-md-block">{{nav.title}}</span>
                                        </a>
                                        <div aria-labelledby="dropdownMenuButton"
                                             class="dropdown-menu dropdown-menu-left"
                                             style="max-height: calc(100vh - 130px);transform: translate3d(0px, 50px, 0px);overflow-y: auto;overflow-x: hidden;min-width: 145px;">

                                            <h6 class="dropdown-header" v-if="nav.type === 'list'">
                                                <i class="fas fa-align-left mr-2"></i>
                                                Lists</h6>
                                            <h6 class="dropdown-header" v-else-if="nav.type === 'board'">
                                                <i class="far fa-clipboard mr-2"></i>
                                                Board</h6>

                                            <!--                                        <a style="max-height: calc(100vh - 190px);transform: translate3d(0px, 50px, 0px);overflow: auto;">-->
                                            <span v-for="nav_list in nav.lists">
                                                <span :id="'list'+nav_list.id"
                                                      v-if="nav_list.is_delete == 2 || nav_list.is_delete == 0"
                                                      :class="(nav.type === 'board') ? 'board'+nav_list.id : '' "
                                                      @click="setListId(nav_list,nav.id,nav_list.description,nav.type)"
                                                      class="dropdown-item">
                                                    <a href="javascript:void(0)" v-if="nav.type === 'list'">
                                                        {{nav_list.list_title}}
                                                    </a>
                                                    <!--                                                     <a href="javascript:void(0)" v-else>{{nav_list.board_title}}</a>-->
                                                 </span>
                                                <span :id="'list'+nav_list.id"
                                                      v-else-if="!nav_list.is_delete"
                                                      :class="(nav.type === 'board') ? 'board'+nav_list.id : '' "
                                                      @click="setListId(nav_list,nav.id,nav_list.description,nav.type)"
                                                      class="dropdown-item"> {{nav_list.is_delete}}
                                                    <!--                                                    <a href="javascript:void(0)" v-if="nav.type === 'list'">{{nav_list.list_title}} </a>-->
                                                     <a href="javascript:void(0)">{{nav_list.board_title}}</a>
                                                 </span>
                                            </span>
                                            <!--                                        </a>-->


                                            <div class="dropdown-divider"></div>

                                            <a @click="addListModel(nav.id)" class="dropdown-item"
                                               href="Javascript:void(0)"
                                               v-if="nav.type === 'list'">
                                                <i class="fa fa-fw text-left fa-plus-circle"></i>
                                                Create List
                                            </a>

                                            <a @click="addBoardModel(nav.id)" class="dropdown-item"
                                               href="Javascript:void(0)"
                                               v-else-if="nav.type === 'board'">
                                                <i class="fa fa-fw text-left fa-plus-circle"></i>
                                                Create Board
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </template>
                        </div>
                    </div>
                    <div>
                        <ul class="navbar-nav ml-4 nav-bar-right" style="padding-right: 10px;">
                            <li class="nav-item search-nav-icon" v-if="list_selected.type !== 'single' && list_selected.type !== 'rules' ">
                                <button class="btn btn-default" type="submit"
                                        @click="showSearchInputField" title="Find Task" data-toggle="tooltip"
                                        style="padding-right: 32px; padding-left: 7px;background: none">
                                    <i class="fal fa-file-search" style="padding-left: 9px;font-size: 22px;"></i>
                                </button>
                            </li>
                            <li class="nav-item search-nav-icon" v-if="list_selected.type === 'board'">
                                <button class="btn btn-default" @click="HideShowBoardTitle" type="submit"
                                        style="padding-right: 32px; padding-left: 7px;background: none;"
                                        title="'Hide' or 'Show' Board Title" data-toggle="tooltip">
                                    <i class="fal fa-eye" style="padding-left: 9px;font-size: 22px;"></i>
                                </button>
                            </li>
                            <li class="nav-item" style="margin-right:10px;"
                                v-if="list_selected.type == 'list' || list_selected.type == 'board' || overview === 'overview'">
                                <a @click="" class="text-center nav-link"
                                   href="Javascript:void(0)">
                                    <span class="pull-right dropdown-toggle" data-toggle="dropdown">Option</span>
                                    <div aria-labelledby="dropdownMenuButton"
                                         class="dropdown-menu dropdown-menu-right dropdown-menu-custom">


                                        <template
                                            v-if="overview === 'overview'">
                                            <h6 class="dropdown-header text-uppercase">Action For Overview</h6>
                                            <span class="dropdown-item custom-dropdown-item">
                                                <a :href="'/list-pdf-create/overview/'+ projectId" target="_blank">
                                                    <i class="fal fa-file-pdf mr-2"></i>
                                                    Create Overview PDF </a>
                                           </span>
                                            <span class="dropdown-item custom-dropdown-item">
                                            <a @click="addListFromOverview">
                                                <i class="fal fa-layer-plus mr-2"></i>
                                                Add List to Overview</a>
                                        </span>
                                        </template>
                                        <template
                                            v-else-if="list_selected.type == 'list' || list_selected.type == 'board'">
                                            <h6 class="dropdown-header text-uppercase">Action For <span
                                                v-if="list_selected.type === 'board'">Board</span> <span
                                                v-else-if="list_selected.type === 'list'">List</span></h6>
                                            <div class="dropdown-divider"></div>
                                            <span class="dropdown-item custom-dropdown-item" @click="UpdateListModel">
                                        <a href="javascript:void(0)">
                                            <i class="fal fa-edit mr-2"></i>
                                            Edit  <span
                                            v-if="list_selected.type === 'board'">Board</span>  <span
                                            v-else>List</span></a>
                                    </span>
                                            <span class="dropdown-item custom-dropdown-item">
                                        <a href="javascript:void(0)" @click="MoveListTOAnotherNav(list_selected.type)">
                                            <i class="fal fa-expand-arrows mr-2"></i>
                                            Move
                                            <span v-if="list_selected.type === 'board'">Board</span>  <span
                                            v-else>List</span> to Another Nav </a>
                                    </span>
                                            <span class="dropdown-item custom-dropdown-item"
                                                  @click="DeleteListOrBoard(list_selected.type,'delete')">
                                        <a href="javascript:void(0)">
                                            <i class="fal fa-trash-alt mr-2"></i>
                                            <span v-if="list_selected.type === 'board'"> Delete with all card</span>
                                            <span v-else>Remove from task view</span></a>
                                    </span>
                                            <span class="dropdown-item custom-dropdown-item"
                                                  @click="DeleteListOrBoard(list_selected.type,'move')">
                                        <a href="javascript:void(0)">
                                            <i class="fal fa-person-carry mr-2"></i>
                                            Delete & move <span
                                            v-if="list_selected.type === 'board'">Card</span>  <span
                                            v-else>Task</span> </a>
                                    </span>
                                            <span class="dropdown-item custom-dropdown-item"
                                                  v-if="list_selected.type === 'list'">
                                        <a :href="'/list-pdf-create/list/'+list_selected.id" target="_blank">
                                            <i class="fal fa-file-pdf mr-2"></i>
                                            Create PDF </a>
                                    </span>

                                            <span class="dropdown-item custom-dropdown-item" @click="importCsvModal()"
                                                  v-if="list_selected.type === 'list'">
                                                <a href="javascript:void(0)">
                                                    <i class="fal fa-file-csv mr-2"></i>
                                                    Import CSV
                                                </a>
                                            </span>
                                        </template>


                                    </div>
                                </a>
                            </li>

                            <li class="nav-item" style="margin-right:10px;">
                                <a @click="shortcutModel" class="d-block d-md-flex text-center nav-link"
                                   href="Javascript:void(0)">
                                    <span class="d-none d-md-block dropdown-toggle">Shortcuts</span>
                                </a>
                            </li>
                            <li class="nav-item" style="margin-right:10px;">
                                <a @click="" class="text-center nav-link"
                                   href="Javascript:void(0)">
                                    <span class="pull-right" @click="ProjectRulesView(projectId)">Rules</span>
                                </a>
                            </li>

                            <li class="nav-item dropdown">
                                <a aria-expanded="false" aria-haspopup="true"
                                   v-if="list_selected.type == 'list' || list_selected.type == 'board'"
                                   class="d-block d-md-flex text-center nav-link dropdown-toggle" data-toggle="dropdown"
                                   href="#">
                                <span class="d-none d-md-block">
                                    <i class="fal fa-filter" style="font-size: 20px;"
                                       v-if="authUser.filter == null"></i>
                                    <i class="fas fa-filter" style="font-size: 20px;color: #6FCF84" v-else></i>
                                </span>
                                </a>
                                <div aria-labelledby="dropdownMenuButton" class="dropdown-menu dropdown-menu-right">

                                    <h6 class="dropdown-header"> Filters</h6>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="javascript:void(0)"
                                       @click="FilterAction('all')">
                                        <i class="far fa-window-restore pr-2"></i>
                                        Reset to Default
                                    </a>
                                    <a class="dropdown-item active" href="javascript:void(0)"
                                       @click="FilterAction('all')">
                                        <i class="far fa-ballot pr-2"></i>
                                        Show All Tasks
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)" @click="FilterAction('my')">
                                        <i class="fal fa-user-visor pr-2"></i>
                                        Show My Tasks
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)"
                                       @click="FilterAction('users_task')">
                                        <i class="fal fa-users-crown pr-2"></i>
                                        Show Users Tasks
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)"
                                       @click="FilterAction('not_assign')">
                                        <i class="fal fa-user-alt-slash pr-2"></i>
                                        No Assigned User
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)"
                                       @click="FilterAction('priority_based')">
                                        <i class="fal fa-exclamation-triangle "></i>
                                        Priority Wise Show/Hide
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)"
                                       v-if="list_selected.type === 'list'"
                                       @click="FilterAction('completed')">
                                        <i class="far fa-ballot-check pr-2"></i>
                                        Show Completed Tasks
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)"
                                       v-if="list_selected.type === 'list'"
                                       @click="FilterAction('hide_completed')">
                                        <i class="fa fa-eye-slash pr-2"></i>
                                        Hide Completed Tasks
                                    </a>
                                    <h6 class="dropdown-header"> Sort</h6>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item active" href="javascript:void(0)"
                                       @click="FilterAction('asc')">
                                        <i class="fa fa-sort pr-2"></i>
                                        Default
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)" @click="FilterAction('asc')">
                                        <i class="fa fa-sort-up pr-2"></i>
                                        Oldest
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)" @click="FilterAction('desc')">
                                        <i class="fa fa-sort-down pr-2"></i>
                                        Newest
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)"
                                       @click="FilterAction('priority')">
                                        <i class="fal fa-exclamation-triangle "></i>
                                        Sort by Priority
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)"
                                       @click="FilterAction('date-asc')">
                                        <i class="fa fa-sort-down pr-2"></i>
                                        By Due Date Ascending
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)" @click="FilterAction('date')">
                                        <i class="fa fa-sort-up pr-2"></i>
                                        By Due Date Descending
                                    </a>
                                </div>
                            </li>

                            <li class="nav-item dropdown">
                                <a aria-expanded="false" aria-haspopup="true"
                                   class="d-block d-md-flex text-center nav-link"
                                   data-toggle="dropdown" href="#">
                                        <span class="d-none d-md-block">
                                            <i class="far fa-plus-hexagon compltit-blue"
                                               style="font-size: 16px;padding-right:5px;"></i>
                                        </span>
                                    Create Nav
                                </a>
                                <div aria-labelledby="dropdownMenuButton" class="dropdown-menu dropdown-menu-right">

                                    <h6 class="dropdown-header text-uppercase"> Manage Nav</h6>
                                    <a @click="showModelForNavItem" class="dropdown-item" href="javascript:void(0)">
                                        <i class="fa fa-fw text-left fa-btn fa-plus-circle compltit-blue"></i>
                                        Create Nav Item
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <h6 class="dropdown-header text-uppercase"> Update Nav Item</h6>
                                    <span @click="updateNavbarModel(nav)" v-for="nav in AllNavItems">
                                     <a class="dropdown-item"
                                        href="javascript:void(0)"><span>{{nav.title}}</span></a>
                                </span>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </nav>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addListModel" role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title pl-3"> Add New List</h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">List Title</label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" v-model="list.name">
                                <span class="text-danger" v-if="listErrors.name">{{ listErrors.name[0] }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">List Description</label>
                            <div class="col-sm-8">
                                <textarea class="form-control" cols="40" id="" name="" rows="3"
                                          v-model="list.description"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button aria-label="Close" class="btn btn-secondary" data-dismiss="modal" type="button">Cancel
                        </button>
                        <button @click="AddNewList" class="btn save-all ml-3" type="button">Create List</button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addBoardModel" role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title pl-3"> Add New Board</h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Board Title</label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" v-model="list.name">
                                <span class="text-danger" v-if="boardErrors.name">{{ boardErrors.name[0] }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Board Description</label>
                            <div class="col-sm-8">
                                <textarea class="form-control" cols="40" name="" rows="3" v-model="list.description"></textarea>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button aria-label="Close" class="btn btn-secondary mr-2" data-dismiss="modal" type="button">
                            Cancel
                        </button>
                        <button @click="AddNewBoard" class="btn save-all" type="button">Create Board</button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="shortcutModel" role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title pl-3"> Shortcuts</h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body bg-white">
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;" v-if="list_selected.type == 'list'">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default">ENTER</span>
                            </li>
                            <li class="list-group-item">Save and Create New Task</li>
                        </ul>
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;" v-if="list_selected.type == 'list'">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default">TAB</span>
                            </li>
                            <li class="list-group-item">Make Sub Task</li>
                        </ul>
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;" v-if="list_selected.type == 'list'">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default">SHIFT</span>
                                +
                                <span class="badge-pill badge-default">TAB</span>
                            </li>
                            <li class="list-group-item">Make Parent Task</li>
                        </ul>
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default">CTRL</span>
                                +
                                <span class="badge-pill badge-default">C</span>
                            </li>
                            <li class="list-group-item">Copy Task</li>
                        </ul>
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default">CTRL</span>
                                +
                                <span class="badge-pill badge-default">V</span>
                            </li>
                            <li class="list-group-item">Paste Task</li>
                        </ul>
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default">CTRL</span>
                                +
                                <span class="badge-pill badge-default">X</span>
                            </li>
                            <li class="list-group-item">Cut Task</li>
                        </ul>
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default">CTRL</span>
                                +
                                <span class="badge-pill badge-default">M</span>
                            </li>
                            <li class="list-group-item">Move task</li>
                        </ul>
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default">CTRL</span>
                                +
                                <span class="badge-pill badge-default">S</span>
                            </li>
                            <li class="list-group-item">Search</li>
                        </ul>
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;" v-if="list_selected.type == 'list'">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default">CTRL</span>
                                +
                                <span class="badge-pill badge-default">i</span>
                            </li>
                            <li class="list-group-item">Upload Image/File</li>
                        </ul>
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default">CTRL</span>
                                +
                                <span class="badge-pill badge-default">U</span>
                            </li>
                            <li class="list-group-item">Assign User</li>
                        </ul>
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default">Shift</span>
                                +
                                <span class="badge-pill badge-default">#</span>
                            </li>
                            <li class="list-group-item">Add Tag</li>
                        </ul>
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;" v-if="list_selected.type == 'list'">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default">CTRL</span>
                                +
                                <span class="badge-pill badge-default">B</span>
                            </li>
                            <li class="list-group-item">Add Don't Forget Tag</li>
                        </ul>
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default">Delete</span>
                            </li>
                            <li class="list-group-item">Delete Task</li>
                        </ul>
                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default"><i class="fa fa-caret-up"></i></span>
                            </li>
                            <li class="list-group-item">
                                <span class="badge-pill badge-default"><i class="fa fa-caret-down"></i></span>
                            </li>
                            <li class="list-group-item">Move Task Up &amp; Down</li>
                        </ul>

                        <ul class="list-group list-group-horizontal multi-list-group"
                            style="margin-left: 0px !important;">
                            <li class="list-group-item">
                                <span class="badge-pill badge-default"><i class="fa fa-caret-right"></i></span>
                            </li>
                            <li class="list-group-item">
                                <span class="badge-pill badge-default"><i class="fa fa-caret-left"></i></span>
                            </li>
                            <li class="list-group-item">Open &amp; Close Task Details</li>
                        </ul>

                    </div>
                    <div class="modal-footer">
                        <button aria-label="Close" class="btn btn-secondary" data-dismiss="modal" type="button">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addNavItem" role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title pl-3"> Add Nav Item</h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label class="control-label float-right m-t-ng-8 txt_media1">Nav Title</label>
                            </div>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" v-model="navItem.title">
                                <span class="text-danger" v-if="navErrors.title">{{ navErrors.title[0] }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label class="control-label float-right m-t-ng-8 txt_media1">Sort Order</label>
                            </div>
                            <div class="col-sm-8">
                                <input class="form-control" min="0" type="number" v-model="navItem.sort_id">
                                <small class="form-text text-muted">Enter a number from 0-99. This will sort the order
                                    of your navigation items</small>
                            </div>

                        </div>

                        <div class="row form-group">

                            <div class="col-sm-4">
                                <label class="control-label float-right m-t-ng-8 txt_media1">Select Type</label>
                            </div>
                            <div class="col-sm-8">
                                <div class="iradio">
                                    <label>
                                        <input id="optionsRadios1" name="optionsRadios" type="radio" value="list">
                                        &nbsp; List view
                                    </label>
                                    <small class="form-text text-muted">List view will create a navigation item for list
                                        style tasks</small>
                                </div>
                                <div class="iradio">
                                    <label>
                                        <input id="optionsRadios2" name="optionsRadios" type="radio" value="board">
                                        &nbsp; Board View
                                    </label>
                                    <small class="form-text text-muted">Board view will create a navigation item for
                                        kanban boards</small>
                                </div>
                                <span class="text-danger" v-if="navErrors.type">{{ navErrors.type[0] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button aria-label="Close" class="btn btn-secondary mr-2" data-dismiss="modal" type="button">
                            Cancel
                        </button>
                        <button @click="AddNavItem" class="btn save-all" type="button">Create</button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="updateNavItem" role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title pl-3 text-uppercase"> Update <span v-if="update_navItem.type === 'list'">List</span>
                            <span v-else>Board</span> Nav Item</h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label class="control-label float-right m-t-ng-8 txt_media1">Nav Title</label>
                            </div>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" v-model="update_navItem.title">
                                <span class="text-danger" v-if="listErrors.title">{{ listErrors.title[0] }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label class="control-label float-right m-t-ng-8 txt_media1">Sort Number</label>
                            </div>
                            <div class="col-sm-8">
                                <input class="form-control" min="0" type="number" v-model="update_navItem.sort_id">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <div class="action-task">
                            <h2>
                                <span class="btn btn-default pull-right dropdown-toggle action-list-board"
                                      data-toggle="dropdown">
                                    Option
                                </span>

                                <div aria-labelledby="dropdownMenuButton"
                                     class="dropdown-menu dropdown-menu-right dropdown-menu-custom">
                                    <h6 class="dropdown-header text-uppercase">
                                        Action For <span v-if="update_navItem.type === 'list'">List</span> <span v-else>Board</span>
                                    </h6>
                                    <div class="dropdown-divider"></div>
                                    <span class="dropdown-item custom-dropdown-item">
                                        <a href="javascript:void(0)"> <i class="fa fa-trash"></i> Delete with all <span
                                            v-if="update_navItem.type === 'list'">List</span> <span
                                            v-else>Board</span></a>
                                    </span>
                                    <span class="dropdown-item custom-dropdown-item">
                                        <a href="javascript:void(0)"> <i class="fa fa-arrows"></i> Delete & move <span
                                            v-if="update_navItem.type === 'list'">List</span> <span
                                            v-else>Board</span> </a>
                                    </span>

                                </div>

                            </h2>
                        </div>
                        <button aria-label="Close" class="btn btn-secondary mr-1" data-dismiss="modal" type="button">
                            Cancel
                        </button>
                        <button @click="DeleteNavItem" class="btn btn-danger" style="position: absolute;left: 14px;"
                                type="button">Delete
                        </button>
                        <button @click="updateNavItem" class="btn btn-primary mr-2" type="button">Update</button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addListFromOverviewModel"
             role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title pl-3"> Add New List</h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Select List Nav </label>
                            <div class="col-sm-8">
                                <select class="form-control" v-model="list.nav_id">
                                    <option disabled value="0">Select List Nav
                                    </option>
                                    <option :key="index" v-bind:value="navs.id" v-for="(navs, index) in AllNavItems"
                                            v-if="navs.type === 'list'">
                                        {{navs.title}}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">List Title</label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" v-model="list.name">
                                <small class="text-danger" v-if="listErrors.name">{{ listErrors.name[0] }}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">List Description</label>
                            <div class="col-sm-8">
                                <textarea class="form-control" cols="40" name="" rows="3"
                                          v-model="list.description"></textarea>
                            </div>
                        </div>
                        <!--                        <p v-if="addField.error" class="text-danger"></p>-->
                    </div>
                    <div class="modal-footer">
                        <button aria-label="Close" class="btn btn-secondary mr-2" data-dismiss="modal" type="button">
                            Cancel
                        </button>
                        <button @click="AddNewListOverview" class="btn save-all ml-1" type="button">Create</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style>
    .list-group-item {
        border: none !important;
    }
    #shortcutModel .modal-body {
        padding: 0 20px;
    }
</style>

<script>
    import hotkeys from 'hotkeys-js';
    import Swal from 'sweetalert2';

    export default {
        name: "Navbar",
        data() {
            return {
                baseUrl: window.location.origin,
                AllNavItems: [],
                projectId: this.$route.params.projectId,
                list_selected: {
                    id: this.$route.params.id,
                    type: null,
                    name: null,
                    nav_id: null,
                    description: null
                },
                list: {
                    name: null,
                    description: null,
                    nav_id: 0,
                    type: null
                },
                navItem: {
                    title: null,
                    type: null,
                    sort_id: null,
                    project_id: null,
                },
                rules: null,
                overview: null,
                update_navItem: {
                    title: null,
                    type: null,
                    sort_id: null,
                    project_id: null,
                },
                authUser: {
                    filter: null
                },
                search_type: null,
                searchData: {
                    tasks: [],
                    users: []
                },
                Socket: null,
                navErrors: [],
                boardErrors: [],
                listErrors: [],
            }
        },
        mounted() {
            var _this = this;
            _this.AllNavItem();
            _this.getAuthUser();
            Bus.$on('addChildToTask', function () {
                _this.getAuthUser();
            })
            Bus.$on('AddedFilterSuccess', function () {
                _this.getAuthUser();
            })
            Bus.$on('UpdateListOrBoard', function () {
                _this.AllNavItem();
            })
            Bus.$on('listMovetoAnotherlist', function () {
                _this.AllNavItem();
            })
            _this.urlTypeCheck();
            _this.connectSocket();

        },
        methods: {
            urlTypeCheck() {
                let _this = this;
                let index = location.href.lastIndexOf('/');
                let last_url_type = location.href.substring(location.href.lastIndexOf('/') + 1);

                if (last_url_type === 'rules' || last_url_type === 'overview') {
                    _this.overview = null;
                    _this.list_selected.type = 'rules';
                } else {
                    var session_data = JSON.parse(localStorage.selected_nav);
                    _this.overview = null;
                    _this.list_selected.type = session_data.type;
                }
                if (last_url_type === 'lists' || last_url_type === 'lists#') {
                    _this.overview = 'overview';
                    $('.searchList').addClass('searchList-overview')
                }

                if(_this.$route.name == 'single-task-view'){
                    _this.list_selected.type = 'single';
                } else if(_this.$route.name == 'list-view'){
                    _this.list_selected.type = 'list';
                } else if(_this.$route.name == 'board-view'){
                    _this.list_selected.type = 'board';
                } else if(_this.$route.name == 'Project-OverView'){
                    _this.list_selected.type = 'overview';
                }
            },
            connectSocket: function () {
                let app = this;
                if (app.Socket == null) {
                    app.Socket = io.connect(window.socket_url);
                    app.Socket.emit('loginId', 2)
                    app.Socket.on('UpdateNav' + app.projectId, function (res) {
                        if (res.project_id == app.projectId) {
                            app.AllNavItem();
                        }
                    })
                    app.Socket.on('DeleteNav' + app.projectId, function (res) {

                        if (res.project_id == app.projectId) {
                            app.AllNavItem();
                            app.$router.push({
                                name: 'Project-OverView',
                                params: {projectId: app.projectId, type: 'lists'}
                            });
                        }
                    })

                }
            },
            getAuthUser() {
                var _this = this;
                axios.get('/api/auth-user')
                    .then(response => response.data)
                    .then(response => {
                        _this.authUser = response.user;
                    })
                    .catch(error => {

                    });
            },
            showSearchInputField() {
                this.search_type = (this.list_selected.type === 'overview') ? 'all' : 'this';
                $('.searchList').toggle();
                $('.searchTaskList').focus();

            },
            searchDataFormTask(e) {
                var value = e.target.value;
                var _this = this;
                if (value.charAt(0) === '@') {
                    value = value.substr(1);

                    axios.get('/api/task-list/all-suggest-user')
                        .then(response => response.data)
                        .then(response => {
                            _this.searchData.users = response.search_user;
                        })
                        .catch(error => {
                            console.log('All suggest user api not working')
                        })

                    if (value.length > 0) {
                        axios.post('/api/task-list/search-result', {'user_name': value})
                            .then(response => response.data)
                            .then(response => {
                                _this.searchData.users = response.search_user;
                            })
                            .catch(error => {
                                console.log('Api is drag and drop not Working !!!')
                            });
                    }
                    $('#myUL').removeClass('myUL-show').addClass('myUL');
                    $('#myUL-user').addClass('myUL-user');
                } else if (value.charAt(0) === '') {
                    $('#myUL-user').removeClass('myUL-user').addClass('myUL-user-hide');
                    $('#myUL').removeClass('myUL-show').addClass('myUL');
                } else {
                    var nav_type = JSON.parse(localStorage.selected_nav);
                    axios.post('/api/task-list/search-result', {
                        'text': value,
                        'project_id': _this.projectId,
                        list_id: nav_type.list_id,
                        type: (_this.search_type === 'all') ? 'overview' : nav_type.type,
                    })
                        .then(response => response.data)
                        .then(response => {
                            _this.searchData.tasks = response.search_tasks;
                        })
                        .catch(error => {
                            console.log('Api is drag and drop not Working !!!')
                        });
                    $('#myUL').removeClass('myUL').addClass('myUL-show');

                }
            },
            FilterAction(type) {
                let _this = this;
                if (_this.list_selected.type === 'list') {
                    Bus.$emit('FilterActionInList', {type: type})
                } else if (_this.list_selected.type === 'board') {
                    Bus.$emit('FilterActionInBoard', {type: type})
                }
                setTimeout(function () {
                    _this.getAuthUser();
                },100)
            },
            showModelForNavItem() {
                $("#addNavItem").modal('show');
                $('input[name="optionsRadios"]').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_square-blue',
                    increaseArea: '20%' // optional
                });
            },
            shortcutModel() {
                $("#shortcutModel").modal('show');
            },
            addListFromOverview() {
                $("#addListFromOverviewModel").modal('show');
            },
            AddNavItem() {
                var _this = this;
                _this.navItem.project_id = _this.projectId;
                _this.navItem.type = $('input[name="optionsRadios"]:checked').val();

                axios.post('/api/nav-item/add-new', _this.navItem)
                    .then(response => response.data)
                    .then(response => {
                        if (response.status === 'exists') {
                            Swal.fire("Already Exist! ", "Enter Another Board Name !", "warning")
                        }else if (response.status === 500){
                            _this.navErrors = response.error;
                        }else {
                            $("#addNavItem").modal('hide');
                            _this.AllNavItem();
                            _this.navItem.title = null;
                            _this.navItem.type = null;
                            _this.navItem.sort_id = null;
                            _this.navItem.project_id = null;
                        }

                    })
                    .catch(error => {
                        console.log('Api for move down task not Working !!!')
                    });
            },
            AllNavItem() {
                var _this = this;
                axios.get('/api/nav-item/' + _this.projectId)
                    .then(response => response.data)
                    .then(response => {
                        _this.AllNavItems = response.success;
                        _this.rules = response.rules;
                        _this.$emit('getNavBars', {AllNavItem: _this.AllNavItems});
                        setTimeout(function () {
                            $('.dropdown').on('show.bs.dropdown', function () {
                                $('.nav-train-station').addClass('stop-train');
                            });
                            $('.dropdown').on('hide.bs.dropdown', function () {
                                $('.nav-train-station').removeClass('stop-train');
                            });
                        }, 500);

                    })
                    .catch(error => {
                        console.log('Api for move down task not Working !!!')
                    });
            },
            updateNavbarModel(data) {
                this.nav_id = data.id;
                this.update_navItem.title = data.title;
                this.update_navItem.type = data.type;
                this.update_navItem.sort_id = data.sort_id;
                this.update_navItem.nav_id = data.id;
                this.update_navItem.project_id = data.project_id;

                $("#updateNavItem").modal('show');
                $('input[name="optionsRadios"]').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_square-blue',
                    increaseArea: '20%' // optional
                });

            },
            DeleteNavItem() {
                var _this = this;
                Swal.fire({
                    title: "Are you sure?",
                    text: "If you delete this nav all lists and tasks under the nav will be deleted.",
                    type: "warning",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.value) {
                        axios.post('/api/nav-item/delete', _this.update_navItem)
                            .then(response => response.data)
                            .then(response => {
                                $("#updateNavItem").modal('hide');
                                localStorage.selected_nav = JSON.stringify({
                                    list_id: null,
                                    nav_id: null,
                                    title: '',
                                    description: '',
                                    project_id: _this.projectId,
                                    type: 'overview'
                                });
                                window.location.reload();
                            })
                            .catch(error => {
                                console.log('Api for move down task not Working !!!')
                            });
                    }
                });


            },
            updateNavItem() {
                var _this = this;
                axios.post('/api/nav-item/update', _this.update_navItem)
                    .then(response => response.data)
                    .then(response => {
                        if (response.status === 500){
                            _this.listErrors = response.error;
                        } else {
                            _this.AllNavItem();
                            $("#updateNavItem").modal('hide');
                        }

                    })
                    .catch(error => {
                        console.log('Api for move down task not Working !!!')
                    });
            },
            addListModel(id) {
                this.nav_id = id;
                $("#addListModel").modal('show');
            },
            addBoardModel(id) {
                this.list.name = '';
                this.list.description = '';
                this.nav_id = id;
                $("#addBoardModel").modal('show');
            },
            SearchTaskByAssignedUser(id, name) {
                $('.searchTaskList').val('@' + name);
                var _this = this;
                var nav_type = JSON.parse(localStorage.selected_nav);
                axios.post('/api/task-list/search-result', {
                    'user_id': id,
                    p_id: _this.projectId,
                    list_id: nav_type.list_id,
                    type: (_this.search_type === 'all') ? 'overview' : nav_type.type,
                })
                    .then(response => response.data)
                    .then(response => {
                        _this.searchData.tasks = response.search_tasks;
                        $('#myUL-user').removeClass('myUL-user');
                        $('#myUL').removeClass('myUL').addClass('myUL-show');

                    })
                    .catch(error => {
                        console.log('drag and drop api is not Working !!!')
                    });
            },
            searchBYType() {
                var _this = this;
                var value = $('.searchTaskList').val();
                var nav_type = JSON.parse(localStorage.selected_nav);

                $('#myUL').removeClass('myUL').addClass('myUL-show');
            },
            selectTaskFromTaskTreeList(task) {
                var nav = JSON.parse(localStorage.selected_nav);
                if (nav.type === 'list') {
                    $('.eachItemRow').removeClass('clicked');
                    $('#click' + task.id).addClass('clicked');
                    var target = document.getElementById('TaskListAndDetail');
                    if ($('#click' + task.id).length > 0) {
                        var top = $('#click' + task.id)[0].getBoundingClientRect().top + target.scrollTop - 241;
                        target.scrollTo(0, top);
                    }
                } else if (nav.type === 'board') {
                    $('#card_' + task.id).click();
                    var target = document.getElementById('coll' + task.board_parent_id);
                    if ($('#card_' + task.id).length > 0) {
                        var top = $('#card_' + task.id)[0].getBoundingClientRect().top + target.scrollTop - 241;
                        target.scrollTo(0, top);
                    }
                } else {

                }
            },

            showSingleView(task) {
                var _this = this;
                var nav_type = JSON.parse(localStorage.selected_nav);
                let routeData = this.$router.resolve({
                    name: 'single-task-view',
                    params: {projectId: task.project_id, type: 'lists', task_id: btoa(task.id), id: task.list_id != null ? task.list_id : task.multiple_board_id  }
                });
                window.open(routeData.href, '_blank');
            },
            HideShowBoardTitle: () => {
                $('#col10').css('height', 'calc(100vh - 177px)');
                $('.smooth-dnd-container').css('height', 'calc(100vh - 262px)');

                $('#board_title').fadeIn();
                setTimeout(function () {
                    $('#board_title').fadeOut(1000);
                    $('#col10').css('height', 'calc(100vh - 104px)');
                    $('.smooth-dnd-container').css('height', 'calc(100vh - 188px)');
                }, 2000)
            },
            setListId(navList, nav_id, description, type) {
                $('.searchList').removeClass('searchList-overview')
                let _this = this;
                _this.nav_id = nav_id;
                _this.list_selected.id = navList.id;
                _this.list_selected.description = description;
                _this.list_selected.nav_id = nav_id;
                var title = (type === 'list') ? navList.list_title : navList.board_title;
                _this.list_selected.name = title;
                let data = {
                    list_id: navList.id,
                    nav_id: nav_id,
                    title: title,
                    description: description,
                    type: type
                }
                localStorage.selected_nav = JSON.stringify({
                    list_id: navList.id,
                    nav_id: nav_id,
                    title: title,
                    description: description,
                    project_id: _this.projectId,
                    type: type
                });
                if (type === 'list') {
                    _this.overview = null;
                    if (_this.$route.path != "/project-dashboard/"+_this.projectId+"/list/"+navList.id){
                        $('#loder-hide').fadeIn();
                    }
                    _this.list_selected.type = 'list';
                    _this.$router.push({
                        name: 'list-view',
                        params: {
                            projectId: _this.projectId,
                            id: navList.id,
                            nav_id: nav_id,
                            title: title,
                            description: description,
                        }
                    });
                    setTimeout(function () {
                        $('.searchList').hide();
                    }, 500)
                } else if (type === 'board') {
                    _this.overview = null;
                    if (_this.$route.path != "/project-dashboard/"+_this.projectId+"/board/"+navList.id){
                        $('#loder-hide').fadeIn();
                    }
                    _this.list_selected.type = 'board';
                    _this.$router.push({
                        name: 'board-view',
                        params: {
                            projectId: _this.projectId,
                            id: navList.id,
                            nav_id: nav_id,
                            title: title,
                            description: description,
                        }
                    });
                    setTimeout(function () {
                        $('.searchList').hide();
                    }, 500)
                }


            },

            ShowOverView() {
                let _this = this;
                this.overview = 'overview';
                this.list_selected.type = null;
                $('.searchList').addClass('searchList-overview')
                this.$router.push({name: 'Project-OverView', params: {projectId: _this.projectId, type: 'lists'}});
            },
            setOverview() {
                let _this = this;
                $('.searchList').hide();
                $('.searchList').addClass('searchList-overview')
                localStorage.selected_nav = JSON.stringify({
                    list_id: null,
                    nav_id: null,
                    title: '',
                    description: '',
                    project_id: this.projectId,
                    type: 'overview'
                });
                _this.list_selected.type = 'overview';
                this.ShowOverView();
            },
            ProjectRulesView(pId) {
                var _this = this
                _this.list_selected.type = 'rules';
                this.$router.push({name: 'Rules', params: {projectId: this.projectId}});
            },
            UpdateRule(id) {
                this.$emit('getList', {
                    list_id: 0,
                    nav_id: 0,
                    title: 0,
                    description: '',
                    type: 'rules',
                    rules_id: id,
                    action_type: 'update',
                })
            },
            AddNewList() {
                let _this = this;
                this.list.project_id = this.projectId;
                this.list.nav_id = this.nav_id;
                axios.post('/api/list-add', this.list)
                    .then(response => response.data)
                    .then(response => {
                        if (response.status === 'exists') {
                            Swal.fire("Already Exist! ", "Enter Another Board Name !", "warning")
                        }else if (response.status === 500){
                            _this.listErrors = response.error;
                        } else {
                            this.AllNavItem();
                            this.multiple_list = response.multiple_list;
                            _this.$router.push({
                                name: 'list-view',
                                params: {
                                    title: response.id.list_title,
                                    description: response.id.description,
                                    projectId: _this.projectId,
                                    id: response.id.id,
                                    nav_id: response.id.nav_id,
                                }
                            });
                            $("#addListModel").modal('hide');
                            this.list.name = null;
                            this.list.description = null;
                        }
                    })
                    .catch(error => {
                        console.log('Add list api not working!!')
                    });
            },
            AddNewListOverview() {
                var _this = this;
                this.list.project_id = this.projectId;
                if (_this.list.name !== null && _this.list.description !== null && _this.list.nav_id !== 0) {
                    axios.post('/api/list-add', this.list)
                        .then(response => response.data)
                        .then(response => {

                            if (response.status === 'exists') {
                                Swal.fire("Already Exist! ", "Enter Another Board Name !", "warning")
                            }else if (response.status === 500){
                                _this.listErrors = response.error;
                            } else {
                                _this.multiple_list = response.multiple_list;
                                _this.AllNavItem();
                                $("#addListFromOverviewModel").modal('hide');
                                _this.list.name = null;
                                _this.list.nav_id = 'Select list Nav';
                                _this.list.description = null;
                                _this.$emit('update_overview');
                            }
                        })
                        .catch(error => {
                            console.log('Add list api not working!!')
                        });
                }

            },
            AddNewBoard() {
                let _this = this
                _this.list.project_id = _this.projectId;
                _this.list.nav_id = _this.nav_id;
                axios.post('/api/board-add', _this.list)
                    .then(response => response.data)
                    .then(response => {
                        if (response.status === 'exists') {
                            Swal.fire("Already Exist! ", "Enter Another Board Name !", "warning")
                        }else if (response.status === 500){
                            _this.boardErrors = response.error;
                        }else {
                            $("#addBoardModel").modal('hide');
                            _this.multiple_list = response.multiple_board;
                            _this.AllNavItem();
                            _this.list.name = null;
                            _this.list.description = null;
                            _this.$router.push({
                                name: 'board-view',
                                params: {
                                    projectId: _this.projectId,
                                    id: response.id.id,
                                    nav_id: response.id.nav_id,
                                    title: response.id.list_title,
                                    description: response.id.description,
                                }
                            });


                            setTimeout(function () {
                                $('.board' + response.id.id).click();
                            }, 300);
                        }
                    })
                    .catch(error => {
                        console.log('Add list api not working!!')
                    });
            },
            UpdateListModel() {
                $("#updateListBoardModel").modal('show');
            },
            MoveListTOAnotherNav(type) {
                Bus.$emit('MoveListTOAnotherNav', {type: type})
            },
            DeleteListOrBoard(type, action) {
                var _this = this;
                localStorage.selected_nav = JSON.stringify({
                    list_id: null,
                    nav_id: null,
                    title: '',
                    description: '',
                    project_id: _this.projectId,
                    type: 'overview'
                });
                if (type === 'list') {
                    Bus.$emit('DeleteList', {type: type, action: action})
                } else if (type === 'board') {
                    Bus.$emit('DeleteBoard', {type: type, action: action})
                }

            },
            DownloadTaskPDF() {
                this.$emit('DownloadTaskPDF')
            },
            HideDetails() {
                $('#task_width').addClass('task_width').removeClass('task_widthNormal');
                $('#details').addClass('details').removeClass('detailsShow');
            },

            importCsvModal() {
                Bus.$emit('importCsvModal');
            }

        },
        created() {
            var _this = this;
            hotkeys('ctrl+s', function (event, handler) {
                event.preventDefault();
                switch (handler.key) {
                    case "ctrl+s":
                        if(_this.$route.name != 'single-task-view'){
                            _this.showSearchInputField();
                        }
                        break;
                    case "ctrl+o":
                        break;
                    default:
                        break;
                }
            });
        },
        directives: {},
        watch: {
            '$route.params.projectId': {
                handler: function (projectId) {
                    var _this = this;
                    _this.projectId = projectId;
                },
                deep: true,
                immediate: true
            }
        }

    }
</script>

<style scoped>
</style>

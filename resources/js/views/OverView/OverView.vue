<template>
    <main>
        <div class="container">
            <div class="loder" id="loder-hide">
                <div class="foo foo1">
                    <div class="circle"></div>
                </div>
                <div class="foo foo2">
                    <div class="circle"></div>
                </div>
            </div>
            <ul class="nav nav-tabs nav-float" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link" :class="(tab_type === 'lists') ? 'active' : ''" id="home-tab" data-toggle="tab"
                       href="#home" role="tab"
                       aria-controls="home" aria-selected="true" @click="gotoTabLink('lists')">
                        All List
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="(tab_type === 'files') ? 'active' : ''" id="profile-tab"
                       data-toggle="tab" href="#profile" role="tab"
                       aria-controls="profile"
                       @click="gotoTabLink('files')" aria-selected="false">
                        Files
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="(tab_type === 'comments') ? 'active' : ''" id="contact-tab"
                       data-toggle="tab" href="#contact" role="tab"
                       aria-controls="contact"
                       @click="gotoTabLink('comments')" aria-selected="false">
                        Comments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="(tab_type === 'logs') ? 'active' : ''" id="contact-logs"
                       data-toggle="tab" href="#logs" role="tab"
                       aria-controls="contact"
                       @click="gotoTabLink('logs')" aria-selected="false">
                        All Logs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="(tab_type === 'members') ? 'active' : ''" id="contact-team"
                       data-toggle="tab" href="#team" role="tab"
                       aria-controls="contact"
                       @click="gotoTabLink('members')" aria-selected="false">
                        Project Members
                    </a>
                </li>
            </ul>
            <div class="tab-content overview-scroll" id="myTabContent">
                <div class="tab-pane fade " :class="(tab_type === 'lists') ? 'show active' : ''" id="home"
                     role="tabpanel" aria-labelledby="home-tab">
                    <div class="accordion" id="listWithHandle">
                        <template v-for="list in All_list">
                            <div class="card overview-list"
                                 v-if="list.is_delete == 1 || list.is_delete == 0"
                                 :data-id="list.id">
                                <div class="card-header overview-list-header" id="headingOne" data-toggle="collapse"
                                     data-target="" aria-expanded="false"
                                     aria-controls="collapseOne">
                                    <div class="col-md-12">
                                        <i class="fad fa-arrows  sort-trigger" data-toggle="tooltip"
                                           title="Change sort-order lists" style="padding: 10px 0;"></i>
                                        <h2 style="margin: 10px 15px;cursor: pointer;float:left;"
                                            data-placement="bottom"
                                            @click="dataCollapse('#collapse'+list.id,list.id)" data-toggle="tooltip">
                                            {{list.list_title}}
                                        </h2>
                                        <a href="#" @click="ShowList(list.id)" style="padding: 15px;float: left;">
                                            <i class="fal fa-angle-double-right"></i> View
                                        </a>
                                        <span class="option-btn">
                                        <span data-toggle="dropdown" class="dropdown-toggle-split col-md-12 opacity"
                                              style="padding: 10px;float: right;">
                                            <i class="fad fa-ellipsis-h"></i>
                                        </span>
                                    <div class="dropdown-menu overview-dropdown dropdown-menu-right">
                                        <div class="collapse show switchToggle">
                                            <a href="#" class="dropdown-item overview-option"
                                               @click="ShowList(list.id)">
                                                <i class="fas fa-align-left mr-2" aria-hidden="true"></i>
                                                View List
                                            </a>
                                            <a href="#" class="dropdown-item overview-option" @click="EditList(list)">
                                                <i class="fal fa-edit mr-2" aria-hidden="true"></i>
                                                Edit List
                                            </a>
                                            <a href="#" class="dropdown-item overview-option" @click="DeleteList(list)">
                                                <i class="fal fa-trash-alt mr-2" aria-hidden="true"></i>
                                                Remove From Overview
                                            </a>
                                            <a :href="'/list-pdf-create/list/'+list.id"
                                               class="dropdown-item overview-option">
                                               <i class="fal fa-download mr-2" aria-hidden="true"></i>
                                                Download PDF
                                            </a>
                                            <a href="#" class="dropdown-item overview-option"
                                               v-if="list.is_delete === 1"
                                               @click="RestoreList(list.id)">
                                                <i class="far fa-trash-undo-alt mr-2"></i>
                                                Restore this list
                                            </a>
                                        </div>
                                    </div>
                                </span>
                                    </div>
                                    <div class="col-md-12">
                                        <p style="float:left;clear:both;word-break: break-all">
                                            {{list.description}}
                                        </p>
                                    </div>
                                </div>
                                <div :id="'collapse'+list.id" class="collapse show multi-collapse "
                                     :class="(list.open === 0) ? 'hide-overview-list-task' : 'show-overview-list-task'"
                                     aria-labelledby="headingOne"
                                     data-parent="#listWithHandle" style="padding: 20px 0px ">
                                    <div class="card-body p-0">
                                        <div class="TaskListAndDetails">
                                            <div class="col-11" id="tree_view_list">
                                                <Tree :data="list.tasks" :indent="2"
                                                      :space="0" class="tree4"
                                                      draggable="false"
                                                      cross-tree="cross-tree">
                                                    <div :class="{eachItemRow: true}"
                                                         slot-scope="{data, _id,store,vm}"
                                                         style="font-size: 12px">
                                                        <template v-html="data.html">
                                                <span class="">
                                                    <img :src="baseUrl+'/img/'+data.progress+'.png'" alt="" height="28"
                                                         width="28"
                                                         v-if="(data.progress !== null)"
                                                         :title="(data.complete_tooltip !== '') ? data.complete_tooltip : 'Complete'"
                                                         data-toggle="tooltip"
                                                         class="task-complete-progress left-content">

                                                    <img :src="baseUrl+'/img/'+data.progress+'.png'" alt="" height="28"
                                                         width="28"
                                                         v-if="data.progress === '100'"
                                                         :title="(data.complete_tooltip !== null) ? data.complete_tooltip : 'Complete'"
                                                         data-toggle="tooltip"
                                                         class="task-complete left-content li-opacity ">

                                                </span>
                                                            <span>
                                                        <span class="inp input-hide input-title"
                                                              style="width: 95%;">
                                                            {{data.text}}
                                                        </span>
                                                    </span>
                                                        </template>
                                                    </div>
                                                </Tree>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="tab-pane fade row" :class="(tab_type === 'files') ? 'show active' : ''" id="profile"
                     role="tabpanel" aria-labelledby="profile-tab"
                     style="margin: 0px 15px;">
                    <div class="row" v-if="All_files != null && All_files.data.length">
                        <template v-for="file in All_files.data" v-if="All_files.data.length > 0">
                            <div class="">
                                <div class="card" style="width: 13rem;margin: 10px 15px 0 0;">
                                    <a v-if="file.file_name.toLowerCase().endsWith('.png') || file.file_name.toLowerCase().endsWith('.jpg') || file.file_name.toLowerCase().endsWith('.jpeg') || file.file_name.toLowerCase().endsWith('.gif')"
                                       @click="showImage(file.file_name,file.tasks_id)"
                                       style="height: 120px;" target="_blank">
                                        <img :src="baseUrl+'/storage/'+file.tasks_id+'/'+file.file_name"
                                             v-if="file.file_name.toLowerCase().endsWith('.png') || file.file_name.toLowerCase().endsWith('.jpg') || file.file_name.toLowerCase().endsWith('.jpeg')  "
                                             class="card-img-top" alt="..." height="120">
                                        <img :src="baseUrl+'/img/txt.png'"
                                             v-else-if="file.file_name.toLowerCase().endsWith('.txt')"
                                             class="card-img-top" alt="..." height="120">
                                        <img :src="baseUrl+'/img/pdf.png'"
                                             v-else-if="file.file_name.toLowerCase().endsWith('.pdf')"
                                             class="card-img-top" alt="..." height="120">
                                        <img :src="baseUrl+'/img/file.png'"
                                             v-else-if="file.file_name.toLowerCase().endsWith('.docx')"
                                             class="card-img-top" alt="..." height="120">
                                        <img :src="baseUrl+'/img/txt.png'"
                                             v-else class="card-img-top" alt="..." height="120">
                                    </a>
                                    <a v-else
                                       :href="'/storage/'+file.tasks_id+'/'+file.file_name"
                                       style="height: 120px;" target="_blank">
                                        <img :src="baseUrl+'/storage/'+file.tasks_id+'/'+file.file_name"
                                             v-if="file.file_name.toLowerCase().endsWith('.png') || file.file_name.toLowerCase().endsWith('.jpg') || file.file_name.toLowerCase().endsWith('.jpeg')  "
                                             class="card-img-top" alt="..." height="120">
                                        <img :src="baseUrl+'/img/txt.png'"
                                             v-else-if="file.file_name.toLowerCase().endsWith('.txt')"
                                             class="card-img-top" alt="..." height="120">
                                        <img :src="baseUrl+'/img/pdf.png'"
                                             v-else-if="file.file_name.toLowerCase().endsWith('.pdf')"
                                             class="card-img-top" alt="..." height="120">
                                        <img :src="baseUrl+'/img/file.png'"
                                             v-else-if="file.file_name.toLowerCase().endsWith('.docx')"
                                             class="card-img-top" alt="..." height="120">
                                        <img :src="baseUrl+'/img/txt.png'"
                                             v-else class="card-img-top" alt="..." height="120">
                                    </a>
                                    <div class="card-body" style="padding: 5px 0px 6px 10px;">
                                        <h3 data-toggle="tooltip" :title="file.title" style="cursor: pointer"
                                            @click="ShowList(file.list_id)">
                                        </h3>
                                        <div class="comment_details">
                                            <ul style="margin: 0px !important;">
                                                <li data-toggle="tooltip" :title="file.user.name">
                                                    <i class="fa fa-pencil" style="color: #5299e0;"></i>
                                                    <span class="user">{{file.user.name}}</span>
                                                </li>
                                                <li><i class="fa fa-calendar" style="color: #5299e0;"></i>
                                                    {{file.created_at.substring(0,10)}}
                                                </li>
                                                <li><i class="fa fa-clock-o" style="color: #5299e0;"></i>
                                                    {{file.created_at | relative}}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div v-else style="text-align: center; margin-top: 25px; color: #637588;">
                        <h3>No files added yet!</h3>
                    </div>
                    <template v-if="All_files !== null && All_files.data.length > 0">
                        <template v-if="All_files.last_page > 1">
                            <nav aria-label="Page navigation example" style="padding-top: 39px;">
                                <ul class="pagination justify-content-end">
                                    <li class="page-item "
                                        :class="(All_files.prev_page_url === null) ? 'disabled' : ''">
                                        <a class="page-link" href="javascript:void(0)"
                                           @click="FilePagination(All_files.current_page-1)" tabindex="-1">Previous</a>
                                    </li>
                                    <template v-for="i in All_files.last_page">
                                        <li class="page-item" :class="(All_files.current_page === i) ? 'active' : ''">
                                            <a class="page-link" @click="FilePagination(i)"
                                               href="javascript:void(0)">{{i}}</a>
                                        </li>
                                    </template>

                                    <li class="page-item"
                                        :class="(All_files.last_page === All_files.current_page) ? 'disabled' : ''">
                                        <a class="page-link" @click="FilePagination(All_files.current_page+1)"
                                           href="javascript:void(0)">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </template>
                    </template>
                </div>
                <div class="tab-pane fade" :class="(tab_type === 'comments') ? 'show active' : ''" id="contact"
                     role="tabpanel" aria-labelledby="contact-tab">

                    <!-- comments container -->
                    <div class="comment_block">
                        <div class="new_comment" v-if="All_comments != null && All_comments.length > 0">
                            <template v-for="comment in All_comments">
                                <ul class="user_comment">
                                    <div class="user_avatar">
                                        <img :src="comment.user.photo_url"
                                             v-if="comment.user.photo_url !== null">
                                        <p class="comment-avature user_avatar" v-else>
                                            {{comment.user.name.substring(0,2)}}</p>
                                    </div>
                                    <div class="comment_body">
                                        <p>
                                            <span class="user">{{comment.user.name}} :</span>
                                        <div class="commentDetails"
                                             v-if="comment.attatchment === null || comment.attatchment === '' "
                                             v-html="comment.comment"></div>
                                        <template v-else>
                                            <a :href="baseUrl+'/storage/'+comment.task_id+'/comment/'+comment.attatchment"
                                               data-toggle="tooltip" title="Click For View Attachment"
                                               target="_blank">
                                                <img
                                                    v-if="comment.attatchment.toLowerCase().endsWith('.png') || comment.attatchment.toLowerCase().endsWith('.jpg') || comment.attatchment.toLowerCase().endsWith('.jpeg')"
                                                    :src="baseUrl+'/storage/'+comment.task_id+'/comment/'+comment.attatchment"
                                                    height="100" alt="">
                                                <span v-else>{{comment.attatchment}}</span>
                                            </a>
                                        </template>
                                        </p>
                                        <p>
                                            <span style="cursor : pointer;color: #6495ED"
                                                  @click="ShowList(comment.list_id)">
                                            </span>
                                            Task : {{comment.title}}
                                            <br>
                                            <a class="badge badge-primary" @click="showSingleViewFromTopVar(comment.task)"
                                               style="height: 23px;line-height: 20px;z-index: 99999;float: right;color: white">
                                                View Details
                                            </a>
                                        </p>
                                    </div>

                                    <div class="comment_toolbar">
                                        <div class="comment_details">
                                            <ul>
                                                <li><i class="fa fa-clock-o"></i>
                                                    {{comment.created_at | relative}}
                                                </li>
                                                <li @click="showSingleViewFromTopVar(comment.task)">
                                                    <i class="fa fa-pencil"></i> <span class="user">{{comment.user.name}}</span>
                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                </ul>
                            </template>
                        </div>
                        <div v-else style="text-align: center; margin-top: 25px; color: #637588;">
                            <h3>No comments yet!</h3>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" :class="(tab_type === 'logs') ? 'show active' : ''" id="logs" role="tabpanel" aria-labelledby="contact-logs">
                    <!-- comments container -->
                    <div class="comment_block" style="height: calc(100vh - 145px);">
                        <div>
                            <div class="form-inline" style="position:relative;">
                                <div class="form-group pl-1">
                                    <label for="perPage">Select Per Page Item : &nbsp;</label>
                                    <select class="form-control" id="perPage" name="perPage" v-model="per_page" @change="setPerPageItem">
                                        <option :value="All_logs.total">All</option>
                                        <template v-for="i in Math.floor(All_logs.total/50)">
                                            <option v-if="i === 1" value="50">50</option>
                                            <option v-if="i === 2" value="100">100</option>
                                            <option v-if="i === 3" value="300">300</option>
                                            <option v-if="i === 4" value="500">500</option>
                                            <option v-if="i === 5" value="1000">1000</option>
                                        </template>

                                    </select>
                                </div>
                                <div>&emsp; Total : {{All_logs.total}}</div>
                                <div>
                                    <template v-if="All_logs.last_page > 1">
                                        <nav aria-label="Page navigation example" style="position: absolute;right: 0;top: 0;">
                                            <ul class="pagination justify-content-end" style="padding: 0px;margin: 0px;">
                                                <li class="page-item " :class="(All_logs.prev_page_url === null) ? 'disabled' : ''">
                                                    <a class="page-link" href="javascript:void(0)" @click="LogPagination(All_logs.current_page-1)" tabindex="-1">Previous</a>
                                                </li>
                                                <template v-for="i in pages">
                                                    <li class="page-item" :class="(All_logs.current_page === i) ? 'active' : ''">
                                                        <a class="page-link" @click="LogPagination(i)" href="javascript:void(0)">{{i}}</a>
                                                    </li>
                                                </template>
                                                <li class="page-item" :class="(All_logs.last_page === All_logs.current_page) ? 'disabled' : ''">
                                                    <a class="page-link" @click="LogPagination(All_logs.current_page+1)" href="javascript:void(0)">Next</a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </template>
                                </div>
                            </div>

                        </div>
                        <div class="new_comment">
                            <table class="table table-striped table-bordered">
                                <thead class="save-all">
                                <tr>
                                    <th scope="col" style="width: 4%;font-size: 14px;">#</th>
                                    <th scope="col" style="width: 30%;font-size: 14px;">Title</th>
                                    <th scope="col" style="width: 30%;font-size: 14px;">Log Type</th>
                                    <th scope="col" style="width: 17%;font-size: 14px;">Action By</th>
                                    <th scope="col" style="width: 11%;font-size: 14px;text-align: center">Action At</th>
                                    <th scope="col" style="width: 8%;font-size: 14px;text-align: center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <template>
                                    <tr v-for="(log , key) in All_logs.data">
                                        <td>{{log.id}}</td>
                                        <td><div v-html="log.title"></div></td>
                                        <td><div v-html="log.log_type"></div></td>
                                        <td>{{log.user.name}}</td>
                                        <td class="text-center">{{log.action_at | relative}}</td>
                                        <td class="text-center" style="cursor : pointer">
                                            <a href="javascript:void(0)" v-if="log.action_type === 'softdelete'" data-toggle="tooltip" title="Undo Delete" @click="RestoreDeletedItem(log.id)">
                                                <img src="/img/task-icon/restore.png" alt="" height="20px" width="20px" class="mr-2">
                                            </a>
                                        </td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>

                        </div>
                        <div>
                            <template v-if="All_logs.last_page > 1">
                                <nav aria-label="Page navigation example">
                                    <ul class="pagination justify-content-end">
                                        <li class="page-item " :class="(All_logs.prev_page_url === null) ? 'disabled' : ''">
                                            <a class="page-link" href="javascript:void(0)" @click="LogPagination(All_logs.current_page-1)" tabindex="-1">Previous</a>
                                        </li>
                                        <template v-for="i in pages">
                                            <li class="page-item" :class="(All_logs.current_page === i) ? 'active' : ''">
                                                <a class="page-link" @click="LogPagination(i)" href="javascript:void(0)">{{i}}</a>
                                            </li>
                                        </template>
                                        <li class="page-item" :class="(All_logs.last_page === All_logs.current_page) ? 'disabled' : ''">
                                            <a class="page-link" @click="LogPagination(All_logs.current_page+1)" href="javascript:void(0)">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" :class="(tab_type === 'members') ? 'show active' : ''" id="team" role="tabpanel" aria-labelledby="contact-logs">
                    <!-- comments container -->
                    <div class="comment_block">
                        <div class="new_comment">
                            <span title="Add new team Member" @click="AddMemberToProject" v-if="team.is_owner === team.auth_id">
                                <i class="fa fa-fw text-left fa-btn fa-plus-circle compltit-blue team-member-add" aria-hidden="true"></i>
                            </span>
                            <table class="table table-striped table-bordered">
                                <thead class="save-all">
                                <tr>
                                    <th scope="col" style="width: 40%;font-size: 16px;">Name</th>
                                    <th scope="col" style="width: 45%;font-size: 16px;">Email</th>
                                    <th scope="col" style="width: 15%;font-size: 16px;text-align: center" v-if="team.is_owner === team.auth_id">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="(user , key) in team.team_users">
                                    <tr>
                                        <td>{{user.name}}</td>
                                        <td>{{user.email}}</td>
                                        <td class="text-center" v-if="team.is_owner === team.auth_id">
                                            <template>
                                                <a href="javascript:void(0)" @click="RemoverUserFromTeam(user.id)"
                                                   data-toggle="tooltip" title="Remove From Team"
                                                   class="compltit-blue-a badge badge-danger"
                                                   v-if="team.auth_id !==user.id">
                                                    <i aria-hidden="true" class="fal fa-trash-alt"></i>
                                                </a>
                                                <a href="javascript:void(0)" data-toggle="tooltip"
                                                   :title=" team.auth_id == user.id ? 'You can\'t remove yourself' : 'You can\'t remove user'"
                                                   class="compltit-blue-a badge badge-danger" style="background: gray"
                                                   v-else>
                                                    <i aria-hidden="true" class="fal fa-trash-alt"></i>
                                                </a>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="updateListBoardModelO" role="dialog" tabindex="-1">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title pl-3"> Update {{list.type}} <span
                                class="text-uppercase">[ {{list.name}} ]</span></h5>
                            <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Add your new list here !</p>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Title</label>
                                <div class="col-sm-8">
                                    <input class="form-control" type="text" v-model="list.name">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Description</label>
                                <div class="col-sm-8">
                                    <textarea class="form-control" cols="40" id="" name="" rows="3" v-model="list.description"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button @click="UpdateListOrBoard" class="btn btn-primary ladda-button ladda_update_list_board mr-2" data-style="expand-right">
                                Update
                            </button>
                            <button aria-label="Close" class="btn btn-secondary" data-dismiss="modal" type="button">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="RemoverUserFromTeam" role="dialog" tabindex="-1">
                <div class="modal-dialog " role="document">
                    <div class="modal-content">
                        <div class="modal-header" style="border-radius: 13px;">
                            <h5 class="modal-title">Option List </h5>
                            <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <label class="checkbox_cus_mini">
                                                <input type="radio" class="checkedUser" @click="addFilterToFilter(2)"
                                                       :checked="remove_member_option == 2 ? true : false">
                                                Assign another
                                                <span class="checkmark"></span>
                                            </label>
                                            <div :class="['input-group', isEmailValid()]" class="form-group"
                                                 v-if="remove_member_option==2">
                                                <span class="input-group-addon " id="basic-addon12"
                                                      style="padding-top : 5px">
                                                    <span class="fa fa-envelope"></span>
                                                </span>
                                                <input type="email" @keyup="suggestUsers()" class="form-control" placeholder="Enter email for add member" v-model="add_member_email"/>
                                            </div>

                                            <div class="assignUser" style="margin-top: -14px;" v-if="showAssignUser === 1">
                                                <div class="card">
                                                    <div class="card-body auHeight m-0 p-0">
                                                        <template v-if="allTeamsUsers.length > 0" v-for="user in allTeamsUsers">
                                                            <div class="users-select-assign m-0 row" v-if="remove_member_id !== user.id">
                                                                <div class="col-md-2 pt-1 pl-4">
                                                                    <p class="assignUser-photo">
                                                                        {{(user.name !== null) ? user.name.substring(0,2) : ''}}
                                                                    </p>
                                                                </div>
                                                                <div class="col-md-10 assign-user-name-email"
                                                                     @click="getAssignEmail(user.email)"
                                                                     style="word-break: break-all;padding-right: 10px;">
                                                                    <h5>
                                                                        {{user.name}}<br>
                                                                        <small>{{user.email}}</small>
                                                                    </h5>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <label class="checkbox_cus_mini">
                                                <input type="radio" @click="addFilterToFilter(1)" class="checkedUser" :checked="remove_member_option == 1 ? true : false">
                                                Un-Assign
                                                <span class="checkmark"></span>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-warning mr-2" v-if="remove_member_option ==1"
                                    @click="RemoveTeamUser()" type="button">Remove
                            </button> &nbsp;
                            <button class="btn btn-primary ml-1" v-if="remove_member_option==2"
                                    @click="RemoveTeamUser()" type="button">Assign &
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="AddMemberToProject" role="dialog" tabindex="-1">
                <div class="modal-dialog " role="document">
                    <div class="modal-content">
                        <div class="modal-header" style="border-radius: 13px;">
                            <h5 class="modal-title">Enter email for add user to team</h5>
                            <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body list-model">
                            <div :class="['input-group', isEmailValid()]" class="form-group">
                                <span class="input-group-addon " id="basic-addon1" style="padding-top : 5px">
                                    <span class="fa fa-envelope"></span>
                                </span>
                                <input type="email" class="form-control" placeholder="Enter email for add member" v-model="add_member_email"/>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-primary ml-1" @click="AddNewProjectUser('new')" type="button">
                                Add User To Team
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="RemoveListFromOverview" role="dialog" tabindex="-1">
                <div class="modal-dialog " role="document">
                    <div class="modal-content">
                        <div class="modal-header alert-h" style="border-radius: 13px;">
                            <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body list-model alert-body">
                            <h2 class="modal-title">
                                Remove this list from both Overview and List menu?
                            </h2>
                            <h5>Removing from both will delete all tasks</h5>

                        </div>
                        <div class="modal-footer alert-footer ">
                            <button class="btn btn-primary alert-left-icon" @click="RemoveListFromBoard('list')" type="button">
                                Keep On List Menu
                            </button>
                            <button class="btn btn-danger alert-right-icon" @click="RemoveListFromBoard('both')" type="button">
                                Remove From Both
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="imageModal" role="dialog" tabindex="-1">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" data-toggle="tooltip" title="View Task" style="cursor: pointer" @click="showSingleView()">
                                {{ modalImg[2] }}
                            </h5>
                            <span @click="deletePhoto(modalImg[0],modalImg[1])" class="file-delete" data-toggle="tooltip" title="Delete this file ">
                                <img src="/img/task-icon/trash.png" class="contex-menu-icon">
                            </span>
                            <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <img :src="'/storage/'+modalImg[1]+'/'+modalImg[0]" class="image-auto">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
    import Sortable from 'sortablejs';
    import draggableHelper from 'draggable-helper';
    import {DraggableTree} from 'vue-draggable-nested-tree';
    import * as Ladda from 'ladda';
    import Swal from 'sweetalert2';
    import Helper from '../../helper';

    export default {
        props: ['projectID', 'update_overview'],
        components: {
            Tree: DraggableTree,
            thy: draggableHelper,
            Sortable,
            Ladda
        },
        data() {
            return {
                baseUrl: window.location.origin,
                project_id: this.$route.params.projectId,
                All_list: null,
                All_files: {
                    data: [],
                    last_page: null,
                    current_page: null,
                    first_page_url: null,
                    last_page_url: null,
                    next_page_url: null,
                    per_page: null,
                    prev_page_url: null,
                    total: null,
                    from: null,
                    to: null,
                },
                All_logs: {
                    data: null,
                    last_page: null,
                    current_page: null,
                    first_page_url: null,
                    last_page_url: null,
                    next_page_url: null,
                    per_page: null,
                    prev_page_url: null,
                    total: null,
                    from: null,
                    to: null,
                },
                All_comments: null,
                team: {
                    team_users: [],
                    is_owner: 0
                },
                list: {
                    id: null,
                    name: null,
                    description: null,
                    nav_id: null,
                    type: null
                },
                per_page: 100,
                pages: [],
                modalImg: [],
                remove_member_option: 0,
                reg: /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,24}))$/,

                add_member_email: '',
                remove_member_id: null,
                auth_id: null,
                selected_list: {},
                Socket: null,
                userSeggistion: null,
                allTeamsUsers: [],
                showAssignUser: 0,
                tab_type: this.$route.params.type,
                image_task : null
            }
        },
        mounted() {
            var _this = this;
            $('#loder-hide').fadeIn();
            setTimeout(function () {
                var listWithHandle = document.getElementById('listWithHandle');
                Sortable.create(listWithHandle, {
                    handle: '.sort-trigger',
                    animation: 150,
                    onChange: function (e) {
                    },
                    onEnd: function (evt) {
                        _this.DragDropAndSort(evt);
                    }
                });
                $('[data-toggle="tooltip"]').tooltip();
                $('.searchList').addClass('searchList-overview')
            }, 500);
            _this.allTeamUsers();
            _this.connectSocket();
        },
        created() {
        },
        methods: {
            connectSocket: function () {
                let app = this;
                if (app.Socket === null) {
                    app.Socket = io.connect(window.socket_url);
                    app.Socket.on('updateTask-overview', function (res) {
                        if(res.project_id === app.projectID){
                            if(res.type === 'file'){
                                app.GetAllFiles();
                            } else if(res.type === 'log') {
                                app.GetAllLogs();
                            } else if(res.type === 'comments') {
                                app.GetAllComments();
                            }
                        }
                    })
                }
            },
            getAllList() {
                $('#loder-hide').fadeIn();
                var _this = this;
                axios.get('/api/project-overview/' + _this.project_id)
                    .then(response => response.data)
                    .then(response => {
                        _this.All_list = response.lists;
                        _this.All_list.sort((a, b) => (a.sort_id > b.sort_id) ? 1 : -1)
                        $('#loder-hide').fadeOut();
                        setTimeout(function () {
                            $('[data-toggle="tooltip"]').tooltip();
                        }, 500)
                        axios.get('/api/overview-all-lists/' + _this.project_id)
                            .then(response => response.data)
                            .then(response => {
                                _this.All_list = response.lists;
                                _this.All_list.sort((a, b) => (a.sort_id > b.sort_id) ? 1 : -1)
                            })
                            .catch(error => {
                                console.log('Api is drag and drop not Working !!!')
                            });
                    })
                    .catch(error => {
                        console.log('Api is drag and drop not Working !!!')
                    });
            },
            dataCollapse(id, list_id) {
                axios.post('/api/project-overview/list-open-close', {list_id: list_id})
                    .then(response => response.data)
                    .then(response => {
                        $(id).slideToggle();
                    })
                    .catch(error => {
                        console.log('Api is drag , drop and sort not Working !!!')
                    });
            },
            DragDropAndSort(e) {
                var data = [];
                var a = $('.overview-list');
                $.each(a, function (i, v) {
                    data[i] = $(v).attr('data-id')
                });
                axios.post('/api/project-overview/list-sort', {data: data})
                    .then(response => response.data)
                    .then(response => {
                    })
                    .catch(error => {
                        console.log('Api is drag , drop and sort not Working !!!')
                    });
            },
            EditList(list) {
                this.list.id = list.id;
                this.list.name = list.list_title;
                this.list.description = list.description;
                this.list.nav_id = list.nav_id;
                this.list.type = 'list';
                $("#updateListBoardModelO").modal('show');
            },
            gotoTabLink(type) {
                var _this = this;
                this.$router.push({name: 'Project-OverView', params: {projectId: _this.project_id, type: type}});
            },
            UpdateListOrBoard() {
                var _this = this;
                var l = Ladda.create(document.querySelector('.ladda_update_list_board'));
                l.start();
                axios.post('/api/board-list-update', _this.list)
                    .then(response => response.data)
                    .then(response => {
                        l.stop();
                        _this.getAllList();
                        $("#updateListBoardModelO").modal('hide');
                        Bus.$emit('UpdateListOrBoard')
                    })
                    .catch(error => {
                        console.log('Add list api not working!!')
                    });
            },
            DeleteList(list) {
                var _this = this;
                $('#RemoveListFromOverview').modal('show');
                _this.selected_list = list;
            },
            RemoveListFromBoard(type) {
                let _this = this;
                let list = _this.selected_list;
                if (type === 'both') {
                    let data = {type: 'list', id: list.id, action: 'delete', overview: 0};
                    axios.post('/api/board-list-delete', data)
                        .then(response => response.data)
                        .then(response => {
                            Swal.fire("This List is deleted successfully !", '', "success");
                            _this.getAllList()
                            _this.$emit('updateLatestNav')
                            $('#RemoveListFromOverview').modal('hide');
                            Swal.close();
                        })
                        .catch(error => {
                            console.log('Add list api not working!!')
                        });
                } else {
                    axios.post('/api/board-list-delete', {
                        type: 'list',
                        id: list.id,
                        action: 'delete',
                        overview: 3
                    })
                        .then(response => response.data)
                        .then(response => {
                            Swal.fire("This List is deleted successfully !", "", "success");
                            _this.getAllList()
                            _this.$emit('updateLatestNav')
                            $('#RemoveListFromOverview').modal('hide');
                            setTimeout(function () {
                                Swal.close();
                            }, 1000)
                        })
                        .catch(error => {
                            console.log('Add list api not working!!')
                        });
                }

            },
            RestoreList(list_id) {
                var _this = this;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to restore this list?",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#3085d6',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, Restore it!'
                }).then((result) => {
                    if (result.value) {
                        axios.post('/api/board-list-delete', {type: 'list', id: list_id, action: 'delete', overview: 2})
                            .then(response => response.data)
                            .then(response => {
                                Bus.$emit('UpdateListOrBoard')
                                _this.Socket.emit('UpdateNav', {project_id: _this.project_id})
                                Swal.fire("Complete!", "This List is restore successfully !", "success");
                                _this.getAllList()
                                setTimeout(function () {
                                    Swal.close();
                                }, 1000)
                            })
                            .catch(error => {
                                console.log(error)
                                console.log('Add list api not working!!')
                            });
                    }
                })
            },
            GetAllFiles() {
                var _this = this;
                axios.get('/api/overview-all-files/' + _this.project_id)
                    .then(response => response.data)
                    .then(response => {
                        _this.All_files = response.files;
                        $('#loder-hide').fadeOut();
                        setTimeout(function () {
                            $('[data-toggle="tooltip"]').tooltip();
                        }, 500)
                    })
                    .catch(error => {
                        console.log('Add files api not working!!')
                    });
            },
            FilePagination(page) {
                var _this = this;
                $('#loder-hide').fadeIn();
                _this.All_files.data = null;
                axios.get('/api/overview-all-files/' + _this.project_id + '?page=' + page)
                    .then(response => response.data)
                    .then(response => {
                        _this.All_files = response.files;
                        $('#loder-hide').fadeOut();
                        setTimeout(function () {
                            $('[data-toggle="tooltip"]').tooltip();
                        }, 500)
                    })
                    .catch(error => {
                        console.log('Add files api not working!!')
                    });

            },
            showImage(image, task_id) {
                let _this = this;
                var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                axios.post('/api/single-task', {tz: tz, project_id : _this.$route.params.projectId, id : task_id})
                    .then(response => response.data)
                    .then(response => {
                        var data = response.task;
                        _this.image_task = data;
                        _this.modalImg = [image, task_id,data.text];
                        $("#imageModal").modal();
                    })
                    .catch(error => {
                        console.log(error)
                        console.log('Api for get single task not Working !!!')
                    });

            },
            deletePhoto(img, id) {
                var _this = this;
                Swal.fire(
                    Helper.swalDelete('Are you sure?', 'You want to delete this file !!!')
                ).then((result) => {
                    if (result.value) {
                        axios.post('/api/task-list/delete-img', {'img': img, id: id})
                            .then(response => response.data)
                            .then(response => {
                                _this.Socket.emit('taskUpdate', {
                                    project_id: _this.projectId,
                                    list_id: response.task.list_id,
                                    board_id: response.task.multiple_board_id,
                                    user_id: response.user,
                                    type: 'Delete Photo',
                                    task_id: id
                                })
                                _this.GetAllFiles();
                                $("#imageModal").modal('hide');
                                _this.$toastr.w("Successfully remove file !");
                                setTimeout(function () {
                                    Swal.close();
                                }, 1000)
                            })
                            .catch(error => {
                                console.log('Api for task date update not Working !!!')
                            });
                    }
                })
            },
            showSingleView() {
                var _this = this;
                let routeData = this.$router.resolve({
                    name: 'single-task-view',
                    params: {
                        projectId: _this.$route.params.projectId,
                        type: 'lists',
                        task_id: btoa(_this.image_task.id),
                        id: _this.image_task.list_id != null ? _this.image_task.list_id : _this.image_task.multiple_board_id
                    }
                });
                window.open(routeData.href, '_blank');
            },
            ShowList(id) {
                let _this = this;
                axios.get('/api/list/' + id)
                    .then(response => response.data)
                    .then(response => {
                        let routeData = _this.$router.resolve({
                            name: 'list-view',
                            params: {
                                projectId: response.success.project_id,
                                id: id,
                                nav_id: response.success.nav_id,
                                title: response.success.list_title,
                                description: response.success.description,
                            }
                        });
                        window.open(routeData.href, '_blank');
                    })
                    .catch(error => {
                        console.log('Api is get list data not Working !!!')
                    });

            },
            GetAllComments() {
                var _this = this;
                axios.get('/api/overview-all-comments/' + _this.project_id)
                    .then(response => response.data)
                    .then(response => {
                        _this.All_comments = response.comments;
                        $('#loder-hide').fadeOut();
                        setTimeout(function () {
                            $('[data-toggle="tooltip"]').tooltip();
                        }, 500)
                    })
                    .catch(error => {
                        console.log('Add files api not working!!')
                    });
            },
            CommentPagination(page) {
                var _this = this;
                _this.All_files.data = null;
                axios.get('/api/overview-all-comments/' + _this.project_id + '?page=' + page)
                    .then(response => response.data)
                    .then(response => {
                        _this.All_files = response.files;
                        setTimeout(function () {
                            $('[data-toggle="tooltip"]').tooltip();
                        }, 500)
                    })
                    .catch(error => {
                        console.log('Add files api not working!!')
                    });

            },
            GetAllLogs() {
                var _this = this;
                axios.get('/api/overview-all-logs/' + _this.project_id)
                    .then(response => response.data)
                    .then(response => {
                        _this.All_logs = response.logs;
                        _this.pages = [];
                        if (_this.All_logs.last_page > 5 && _this.All_logs.current_page > 2) {
                            _this.pages.push(_this.All_logs.current_page - 2);
                            _this.pages.push(_this.All_logs.current_page - 1);
                            _this.pages.push(_this.All_logs.current_page);
                            _this.pages.push(_this.All_logs.current_page + 1);
                            _this.pages.push(_this.All_logs.current_page + 2);
                        } else {
                            _this.pages = [1, 2, 3, 4, 5]
                        }
                        if (_this.per_page > _this.All_logs.total) {
                            _this.per_page = _this.All_logs.total;
                        }
                        setTimeout(function () {
                            $('[data-toggle="tooltip"]').tooltip();
                            $('#loder-hide').fadeOut();
                        }, 500)
                    })
                    .catch(error => {
                        console.log('Add files api not working!!');
                    });
            },
            LogPagination(page) {
                var _this = this;
                $('#loder-hide').fadeIn();
                axios.get('/api/overview-all-logs/' + _this.project_id + '?page=' + page + '&per_page=' + _this.per_page)
                    .then(response => response.data)
                    .then(response => {
                        _this.All_logs = response.logs;
                        _this.pages = [];
                        if (_this.All_logs.last_page > 5 && _this.All_logs.current_page > 2 && _this.All_logs.last_page > _this.All_logs.current_page + 1) {
                            _this.pages.push(_this.All_logs.current_page - 2);
                            _this.pages.push(_this.All_logs.current_page - 1);
                            _this.pages.push(_this.All_logs.current_page);
                            _this.pages.push(_this.All_logs.current_page + 1);
                            _this.pages.push(_this.All_logs.current_page + 2);
                        } else if (_this.All_logs.last_page > 5 && _this.All_logs.current_page <= 2) {
                            _this.pages = [1, 2, 3, 4, 5];
                        } else if (_this.All_logs.last_page > 5) {
                            _this.pages = [_this.All_logs.last_page - 4, _this.All_logs.last_page - 3, _this.All_logs.last_page - 2, _this.All_logs.last_page - 1, _this.All_logs.last_page];
                        } else {
                            _this.pages = [];
                            for (var i = 1; i <= _this.All_logs.last_page; i++) {
                                _this.pages.push(i);
                            }
                        }
                        $('#loder-hide').fadeOut();
                    })
                    .catch(error => {
                        console.log('Add files api not working!!')
                    });

            },
            setPerPageItem() {
                $('#loder-hide').fadeIn();
                this.LogPagination(1);
            },
            RestoreDeletedItem(id) {
                var _this = this;
                Swal.fire({
                    title: "Are you sure",
                    text: "You want to undo the delete?",
                    type: "warning",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "Yes, Restore it!"
                }).then((result) => {
                    if (result.value) {
                        axios.post('/api/overview-log/undo-action', {type: 'delete', id: id})
                            .then(response => response.data)
                            .then(response => {
                                if (response.status) {
                                    _this.$toastr.s("Undo successfully Done !");
                                    _this.GetAllLogs();
                                }
                                setTimeout(function () {
                                    Swal.close();
                                }, 1000)
                            })
                            .catch(error => {
                                console.log('undo failed')
                            });
                    }
                });
            },
            getTeamUser() {
                var _this = this;
                axios.post('/api/all-project-users', {project_id: _this.project_id})
                    .then(response => response.data)
                    .then(response => {
                        _this.allTeamsUsers = response.users;
                        _this.team.team_users = response.users;
                        _this.team.is_owner = response.owner;
                        _this.team.auth_id = response.auth_id;
                        setTimeout(function () {
                            $('[data-toggle="tooltip"]').tooltip();
                            $('#loder-hide').fadeOut();
                        }, 500)
                    })
                    .catch(error => {
                        console.log('Team User api not working !!')
                    });
            },
            RemoverUserFromTeam(user_id) {
                var _this = this;
                _this.remove_member_id = user_id;
                axios.post('/api/project-assign-check', {'user_id': user_id, 'project_id': _this.project_id})
                    .then(response => response.data)
                    .then(response => {

                        if (response.assign === 1) {
                            $('#RemoverUserFromTeam').modal('show')
                        } else {
                            Swal.fire('Project user remove! ', '', 'success');
                            setTimeout(function () {
                                Swal.close()
                            }, 1000)
                            _this.getTeamUser();
                        }
                    })
                    .catch(error => {
                        console.log('Team User api not working !!')
                    });
            },
            addFilterToFilter(type) {
                this.remove_member_option = type;
                if (type === 2) {
                    this.showAssignUser = 1;
                } else {
                    this.showAssignUser = 0;
                    this.add_member_email = '';
                }
            },
            AddMemberToProject() {
                $('#AddMemberToProject').modal('show')
            },
            AddNewProjectUser(type) {
                var _this = this;
                if (_this.add_member_email.length > 0 && _this.reg.test(this.add_member_email)) {

                    axios.post('/api/overview-team-member-add', {
                        email: _this.add_member_email,
                        project_id: _this.project_id
                    })
                        .then(response => response.data)
                        .then(response => {
                            Bus.$emit('UpdateTeamUser');
                            _this.add_member_email = '';
                            if (response.success === 1) {
                                Swal.fire('Sorry !!', 'User already added to project! ', 'warning')
                            } else if (response.success === 2) {
                                Swal.fire('Successfully added user to project  ', '', 'success')
                                $('#AddMemberToProject').modal('hide')
                                _this.getTeamUser();
                            } else if (response.success === 3) {
                                Swal.fire('User is not your team member ', '', 'success')
                            } else if (response.success === 0) {
                                Swal.fire('Sorry !!', 'User not found', 'warning')
                            }
                            setTimeout(() => {
                                Swal.close();
                            }, 1000);
                        })
                        .catch(error => {
                            console.log('Add files api not working!!')
                        });
                }
            },
            RemoveTeamUser() {
                var _this = this;
                if (_this.add_member_email.length != 0 && !(this.reg.test(this.add_member_email)) && _this.remove_member_option == 2) {
                    return false;
                } else if (_this.add_member_email.length === 0 && _this.remove_member_option === 2) {
                    return false;
                }

                axios.post('/api/overview-team-member-remove', {
                    email: _this.add_member_email,
                    option: _this.remove_member_option,
                    project_id: _this.project_id,
                    remove_member_id: _this.remove_member_id
                })
                    .then(response => response.data)
                    .then(response => {
                        Bus.$emit('UpdateTeamUser');
                        if (response.success === 0) {
                            Swal.fire('User Not team Member', '', 'warning');
                        } else if (response.success === 1) {
                            _this.getTeamUser();
                            $('#RemoverUserFromTeam').modal('hide')
                            Swal.fire('Assign another user Success', '', 'success');

                        } else if (response.success === 2) {
                            _this.getTeamUser();
                            $('#RemoverUserFromTeam').modal('hide')
                            Swal.fire('Remove user Success', '', 'success');
                        }

                        setTimeout(() => {
                            Swal.close();
                        }, 1000);
                    })

                    .catch(error => {
                        console.log('Add files api not working!!')
                    });

            },
            isEmailValid: function () {
                return (this.add_member_email === "") ? "" : (this.reg.test(this.add_member_email)) ? 'has-success' : 'has-error';
            },

            suggestUsers() {
                let _this = this;
                _this.showAssignUser = 1;
                let data = {
                    name: _this.add_member_email
                };
                axios.post('/api/project-users', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.allTeamsUsers = response.users;

                    }).catch(error => {

                });
            },

            getAssignEmail(email) {
                let _this = this;
                _this.add_member_email = email;
                _this.showAssignUser = 0
            },
            GetAllTabData() {
                let _this = this;
                if (_this.tab_type === 'lists') {
                    _this.getAllList();
                } else if (_this.tab_type === 'files') {
                    _this.GetAllFiles();
                } else if (_this.tab_type === 'comments') {
                    _this.GetAllComments();
                } else if (_this.tab_type === 'logs') {
                    _this.GetAllLogs();
                } else if (_this.tab_type === 'members') {
                    _this.getTeamUser();
                }
            },
            showSingleViewFromTopVar(task) {
                var _this = this;
                var nav_type = JSON.parse(localStorage.selected_nav);
                localStorage.task_view_type = JSON.stringify('comment');
                let routeData = this.$router.resolve({
                    name: 'single-task-view',
                    params: {
                        projectId: task.project_id,
                        type: 'lists',
                        task_id: btoa(task.id),
                        id: task.list_id != null ? task.list_id : task.multiple_board_id
                    }
                });
                window.open(routeData.href, '_blank');
            },
        },
        watch: {
            '$route.params.type': {
                handler: function (type) {
                    var _this = this;
                    _this.tab_type = type;
                    _this.GetAllTabData();
                },
                deep: true,
                immediate: true
            }
        }
    };
</script>

<template>
    <div class="container" id="hide-single-view" v-if="Object.keys(selectedData).length > 0" style="height: calc(100vh - 130px);overflow-y: auto">
        <div class="loder" id="loder-hide">
            <div class="foo foo1">
                <div class="circle"></div>
            </div>
            <div class="foo foo2">
                <div class="circle"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-2">
                <div style="margin-top: 5px">
                    <span v-if="selectedData.multiple_board_id" class="go-details" @click="showOriginalList(selectedData)">
                        Go to Board
                    </span>
                </div>

                <div>
                    <span v-if="selectedData.list_id" class="go-details" @click="showOriginalList(selectedData)">
                        Go to List
                    </span>
                </div>
            </div>
            <div class="col-2">
                <i style="height: 29px; cursor: pointer;"
                   v-if="selectedData.date === '0000-00-00'"
                   class="fal fa-calendar-plus details-title calender-for-details details-header"></i>
                <datepicker
                    :disabled-dates="disabledDates"
                    @selected="updateDate"
                    calendar-button-icon='<i class="outline-event icon-image-preview"></i>'
                    format='dd MMM'
                    input-class="dateCal due-details details-header"
                    v-model="selectedData.date">
                </datepicker>
            </div>
            <div class="col-1">
                <img v-if="selectedData.progress !== null" class="img-responsive details-header"
                     :src="baseUrl+'/img/'+selectedData.progress+'.png'"
                     style="height:30px;width:30px;">

            </div>
            <div class="col-4 text-center">
                <a :class="{'tag-icon': true, 'tag-icon-free': selectedData.tags == undefined || selectedData.tags.length == 0}">
                    <div v-if="selectedData.tags && selectedData.tags.length !== 0" style="display:inline-flex;">
                        <div style="margin-top: 10px;" v-for="(item, tagIndex) in selectedData.tags">
                            <div class="dropdown-toggle-split "
                                 data-toggle="dropdown"
                                 style="padding-right: 0px; padding-left: 1px;" v-if="tagIndex < 2">
                                    <span class="badge badge-danger "
                                          v-if='item == "Dont Forget"'>{{item.text.substring(0,12)}}</span>
                                <span :title="selectedData.tagTooltip"
                                      class="badge badge-success "
                                      data-placement="bottom"
                                      data-toggle="tooltip"
                                      v-bind:style="[{'background': item.color },{'margin-left' : 1 +'px'}]"
                                      style="line-height: 19px;font-size: 12px"
                                      v-else
                                >{{item.text.substring(0,10)}}
                                        <span v-if="item.text.length > 10">..</span>
                                    </span>
                            </div>

                            <div :id="'dropdown1'+selectedData.cardId" class="dropdown-menu dropdown-menu1"
                                 style="width: 250px !important;">

                                <diV class="collapse show switchToggle">
                                    <div class="container-fluid">
                                        <input class="input-group tagInput"
                                               placeholder="Search Tags"
                                               type="text"
                                               v-model="searchTag"
                                               @keyup="searchTags(index, key)">
                                        <vue-tags-input
                                            :allow-edit-tags="true"
                                            :tags="selectedData.tags"
                                            @before-deleting-tag="deleteTag => deleteCardTag(deleteTag,selectedData)"
                                            @tags-changed="newTags => (changeTag(newTags,selectedData))"
                                            v-model="tag"/>

                                        <div class="row">
                                            <div class="col-12 tag-section">
                                                <template v-for="(tag, tagIndx) in selectedData.allTags">
                                                    <li class="badge-pill tags"
                                                        @click="addExistingTag( tagIndx, selectedData.cardId, '')"
                                                        v-bind:style="[{'background': tag.color },{'margin-left' : 1 +'px'}]"
                                                        v-if="tag.text !== 'Dont Forget'">
                                                        {{(tag.title !== undefined) ?tag.title.substring(0,12) :
                                                        ''}}
                                                    </li>
                                                </template>
                                                <li @click="addExistingTag( 0, selectedData.cardId, 'Dont Forget')"
                                                    class="badge-pill tags" style="background: #FB8678"> Dont Forget
                                                </li>
                                            </div>
                                        </div>
                                    </div>

                                </diV>
                            </div>
                        </div>
                    </div>

                    <i class="fal fa-tags dropdown-toggle-split li-opacity" data-toggle="dropdown"
                       style=" height: 29px;cursor: pointer; margin: 9px 5px;" v-else
                       data-original-title="Add Tag"></i>


                    <div class="dropdown-menu dropdown-menu1 " style="width: 250px !important;">
                        <diV class="collapse show switchToggle">
                            <div class="container-fluid">
                                <input class="input-group tagInput"
                                       placeholder="Search Tags"
                                       type="text"
                                       v-model="searchTag"
                                       @keyup="searchTags(index, key)">

                                <vue-tags-input
                                    :allow-edit-tags="true"
                                    :tags="tag1"
                                    @before-deleting-tag="deleteTag => deleteCardTag(deleteTag,selectedData)"
                                    @tags-changed="newTags => (changeTag(newTags,selectedData))"
                                    v-model="tag"/>

                                <div class="row">
                                    <div class="col-12 tag-section">
                                        <template v-for="(tag, tagIndx) in selectedData.allTags">
                                            <li class="badge-pill tags"
                                                @click="addExistingTag( tagIndx, selectedData.cardId ,'')"
                                                v-bind:style="[{'background': tag.color },{'margin-left' : 1 +'px'}]"
                                                v-if="tag.text !== 'Dont Forget'">
                                                {{(tag.title !== undefined) ?tag.title.substring(0,12) : ''}}
                                            </li>
                                        </template>
                                        <li @click="addExistingTag( 0, selectedData.cardId, 'Dont Forget')"
                                            class="badge-pill tags" style="background: #FB8678"> Dont Forget
                                        </li>
                                    </div>
                                </div>
                                <hr>
                                <div class="col-xs-12"
                                     style="margin-top:10px;width: 100%;">
                                    <button @click="showTagManageModel"
                                            class="btn btn-small btn-primary pull-right"
                                            type="submit">
                                        Manage Tag
                                    </button>
                                </div>
                            </div>
                        </diV>
                    </div>
                </a>

            </div>
            <!-- Assign Users -->
            <div class="col-md-3" style="padding: 0px;text-align: center">
                <a class="user dropdown-hide-with-remove-icon">
                    <template v-if="selectedData.assigned_user.length > 0">
                        <span class="assigned_user dropdown-toggle-split "
                              data-toggle="dropdown" v-for="(assign,keyId) in selectedData.assigned_user">
                            <p :title="assign.name"
                               class="assignUser-photo-for-selected text-uppercase"
                               data-placement="bottom" data-toggle="tooltip"
                               style="top: 10px;"
                               v-if="keyId <= 1">
                               {{(assign.name !== null) ? assign.name.substring(0,2) : ''}}
                            </p>

                        </span>
                    </template>
                    <span data-toggle="dropdown" class=" dropdown-toggle-split" style="float: right;" v-else>

                       <i class="fal fa-user-plus li-opacity"
                          style=" height: 29px;cursor: pointer; margin: 9px 11px;"
                          data-original-title="Assign User"></i>

                    </span>

                    <div class="dropdown-menu dropdown-menu-right"
                         style="z-index: 1;right: -50px;left: inherit !important;width: 300px">
                        <diV class="collapse show switchToggle">
                            <div v-if="userSeggistion">
                                <li class="assignUser">
                                    <input class="input-group searchUser"
                                           @keyup="suggestUsers()"
                                           placeholder="Assign user search by: First or last or email"
                                           style="width: 90%; padding: 12px 20px; margin: 10px; display: inline-block; border: 1px solid #ccc;border-radius: 4px; box-sizing: border-box; "
                                           v-model="userSeggistion"
                                           type="text">
                                    <label class="pl-2 label-text">
                                        <span class="assign-user-drop-down-text">
                                            Or invite a new member by email address
                                        </span>
                                    </label>
                                </li>
                                <li class="assignUser auHeight">
                                    <template>
                                        <div v-for="user in allTeamsUsers" v-if="selectedData.assigned_user_ids.includes(user.id) === true"
                                             class="active-user disabled row" disabled style="margin: 0px">
                                            <div class="col-md-3 pt-1 pl-4">
                                                <p class="assignUser-photo">
                                                    {{(user.name !== null) ? user.name.substring(0,2) : ''}}</p>
                                            </div>

                                            <div class="col-md-9 assign-user-name-email">
                                                <h5 style="padding-right: 35px;">{{user.name}}<br>
                                                    <small>{{user.email}}</small>
                                                </h5>
                                            </div>
                                            <a :id="'remove-assign-user'+user.id"
                                               @click="removeAssignedUser(user.id, selectedData.cardId)"
                                               data-toggle="tooltip" title="Remove user from assigned !"
                                               class="remove-assign-user badge badge-danger"
                                               href="javascript:void(0)">
                                                <i class="fal fa-user-times remove-assign-user-icon"></i>
                                            </a>
                                        </div>

                                        <div v-for="user in allTeamsUsers"
                                             v-if="selectedData.assigned_user_ids.includes(user.id) === false"
                                             @click="assignUserToTask(user, selectedData)"
                                             class="row users-select" style="margin: 0px">
                                            <div class="col-md-3 pt-1 pl-4">
                                                <p class="assignUser-photo">
                                                    {{(user.name !== null) ? user.name.substring(0,2) : ''}}</p>
                                            </div>
                                            <div class="col-md-9 assign-user-name-email">
                                                <h5 style="padding-right: 35px;">{{user.name}}<br>
                                                    <small>{{user.email}}</small>
                                                </h5>
                                            </div>

                                            <a :id="'remove-assign-user'+user.id"
                                               data-toggle="tooltip" title="Assign user to task!"
                                               class="remove-assign-user badge badge-success"
                                               href="javascript:void(0)">
                                                <i class="fal fa-user remove-assign-user-icon"></i>
                                            </a>
                                        </div>

                                    </template>
                                </li>
                            </div>
                            <div v-else>
                                <li class="assignUser">
                                    <input class="input-group searchUser"
                                           @keyup="suggestUsers()"
                                           placeholder="Assign user search by: First or last or email"
                                           style="width: 90%; padding: 12px 20px; margin: 10px; display: inline-block; border: 1px solid #ccc;border-radius: 4px; box-sizing: border-box; "
                                           v-model="userSeggistion"
                                           type="text">
                                    <label class="pl-2 label-text">
                                        <span class="assign-user-drop-down-text">
                                            Or invite a new member by email address
                                        </span>
                                    </label>
                                </li>
                                <li class="assignUser auHeight">
                                    <template>
                                        <div v-for="user in selectedData.users" v-if="selectedData.assigned_user_ids.includes(user.id) === true"
                                             class="active-user disabled row" disabled style="margin: 0px">
                                            <div class="col-md-3 pt-1 pl-4">
                                                <p class="assignUser-photo">
                                                    {{(user.name !== null) ? user.name.substring(0,2) : ''}}</p>
                                            </div>

                                            <div class="col-md-9 assign-user-name-email">
                                                <h5 style="padding-right: 35px;">{{user.name}}<br>
                                                    <small>{{user.email}}</small>
                                                </h5>
                                            </div>
                                            <a :id="'remove-assign-user'+user.id"
                                               @click="removeAssignedUser(user.id, selectedData.cardId)"
                                               data-toggle="tooltip" title="Remove user from assigned !"
                                               class="remove-assign-user badge badge-danger"
                                               href="javascript:void(0)">
                                                <i class="fal fa-user-times remove-assign-user-icon"></i>
                                            </a>
                                        </div>

                                        <div v-for="user in selectedData.users"
                                             v-if="selectedData.assigned_user_ids.includes(user.id) === false"
                                             @click="assignUserToTask(user, selectedData)"
                                             class="row users-select" style="margin: 0px">
                                            <div class="col-md-3 pt-1 pl-4">
                                                <p class="assignUser-photo">
                                                    {{(user.name !== null) ? user.name.substring(0,2) : ''}}</p>
                                            </div>
                                            <div class="col-md-9 assign-user-name-email">
                                                <h5 style="padding-right: 35px;">{{user.name}}<br>
                                                    <small>{{user.email}}</small>
                                                </h5>
                                            </div>

                                            <a :id="'remove-assign-user'+user.id"
                                               data-toggle="tooltip" title="Assign user to task!"
                                               class="remove-assign-user badge badge-success"
                                               href="javascript:void(0)">
                                                <i class="fal fa-user remove-assign-user-icon"></i>
                                            </a>
                                        </div>

                                    </template>
                                </li>
                            </div>
                        </diV>
                    </div>
                </a>
            </div>
            <div class="col-12">
                <h5> Created By <b>: {{selectedData.created_by.name}}</b></h5>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h5 class="detailsTitle">
                    <input :id="selectedData.id"
                           @click="makeInput($event,selectedData)"
                           @keyup="saveData($event,selectedData)"
                           class="inp input-hide input-title form-control"
                           type="text"
                           autocomplete="off"
                           v-model="selectedData.data">
                </h5>
            </div>
        </div>
        <!-- Tabs -->
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a aria-controls="home" aria-selected="true" class="nav-link active" data-toggle="tab"
                           href="#home"
                           id="_details" role="tab">Details</a>
                    </li>
                    <li class="nav-item" @click="getFiles(selectedData.cardId)">
                        <a aria-controls="file" aria-selected="false" class="nav-link" data-toggle="tab"
                           href="#file"
                           id="_file" role="tab">Files</a>
                    </li>
                    <li class="nav-item" @click="getComments(selectedData.cardId)" id="comment-nav">
                        <a aria-controls="comment" aria-selected="false" class="nav-link" data-toggle="tab"
                           href="#comment"
                           id="_comment" role="tab">Comments</a>
                    </li>

                    <li class="nav-item">
                        <a @click="showLog" aria-controls="log" aria-selected="false" class="nav-link" data-toggle="tab"
                           href="#log"
                           id="_log" role="tab">Logs</a>
                    </li>
                    <li @click="showChild(selectedData.cardId)" class="nav-item"
                        v-if="(selectedData.childrens.length  > 0 || selectedData.parents.length  > 0)">
                        <a aria-controls="child" aria-selected="false" class="nav-link" data-toggle="tab" href="#child"
                           id="_child" role="tab">Related Tasks</a>
                    </li>

                </ul>
            </div>
        </div>
        <!-- Tab panes -->
        <div class="tab-content">
            <div aria-labelledby="home-tab" class="tab-pane active" id="home" role="tabpanel">
                <hr>
                <!-- Text Description -->
                <div class="row">
                    <div class="col-12">
                        <div class="textAreaExtend">
                            <div class="row">
                                <div class="col-12" style="position: relative">
                                    <h3>Details</h3> <span @click="updateButtonDescription()"  style="position: absolute; top: -2px;right: 15px;" class="btn btn-info">Save</span>
                                </div>
                                <div class="col-12" >
                                    <textarea name="editor" id="editor" v-model="selectedData.description" row="50">
                                        This is my textarea to be replaced with CKEditor.
                                    </textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="submitdetails" id="submitdetails" style="margin-top: 20px">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Comments -->
            </div>

            <div aria-labelledby="log-tab" class="tab-pane" id="file" role="tabpanel" v-if="selectedData.files !== ''">
               <span>
                   <h3 class="p-3">Files</h3>
                   <div class=" comment-section-in-task-details" style="max-height: calc(100vh - 315px);">
                       <div class="row" v-if="selectedData.files !== ''" id='fileSection'
                            style=" max-height: calc(100vh - 390px); width: 100%; overflow: auto;">
                            <div class="col-md-4" v-for="files in selectedData.files">
                               <div class="card-list card">
                                    <span style="padding: 10px;">
                                        <a v-if="files.file_name.toLowerCase().endsWith('.png') || files.file_name.toLowerCase().endsWith('.jpg') || files.file_name.toLowerCase().endsWith('.jpeg') || files.file_name.toLowerCase().endsWith('.gif')"
                                           style="cursor: pointer;position: absolute;"  @click="showImage(files.file_name,files.tasks_id)">
                                            <div style="float: left;"
                                                 v-if="files.file_name.toLowerCase().endsWith('.png') || files.file_name.toLowerCase().endsWith('.jpg') || files.file_name.toLowerCase().endsWith('.jpeg') || files.file_name.toLowerCase().endsWith('.gif')">
                                                <img title="Click To Download" data-toggle="tooltip"
                                                     :src="'/storage/'+selectedData.cardId+'/'+files.file_name"
                                                     height="50" width="50">
                                            </div>
                                        </a>
                                        <a target="_blank" :href="'/storage/'+selectedData.cardId+'/'+files.file_name" v-else
                                           style="cursor: pointer;position: absolute;">
                                            <div style="float: left;"
                                                 v-if="files.file_name.toLowerCase().endsWith('.txt') ">
                                                <img title="Click To Download" data-toggle="tooltip"
                                                     :src="'/img/txt.png'" height="50" width="50">
                                            </div>
                                            <div style="float: left;"
                                                 v-else-if="files.file_name.toLowerCase().endsWith('.pdf') ">
                                                <img title="Click To Download" data-toggle="tooltip"
                                                     :src="'/img/pdf.png'" height="50" width="50">
                                            </div>
                                            <div style="float: left;"
                                                 v-else-if="files.file_name.toLowerCase().endsWith('.doc') || files.file_name.toLowerCase().endsWith('.docx') || files.file_name.toLowerCase().endsWith('.xls') || files.file_name.toLowerCase().endsWith('.xlsx')">
                                                <img title="Click To Download" data-toggle="tooltip"
                                                     :src="'/img/file.png'" height="50" width="50">
                                            </div>
                                            <div style="float: left;" v-else>
                                                <img title="Click To Download" data-toggle="tooltip"
                                                     :src="'/img/attachment.png'" height="50" width="50">
                                            </div>
                                        </a>
                                        <span :title="(files != undefined ) ? files.user.name : ''"
                                              data-toggle="tooltip" style="position: relative; float: right;"
                                              v-html="$helpers.dateFormat(files.created_at)+'<br>&emsp;&emsp;'+files.user.name.split(' ')[0].substring(0,9)"></span>

                                        <span>
                                            <img @click="deleteFile(files.id)"
                                                 style="position: absolute; right: 5px; bottom: 10px;"
                                                 :src="'https://img.icons8.com/color/48/000000/delete-forever.png'"
                                                 height="20" width="20">
                                        </span>
                                    </span>
                                </div>
                            </div>
                       </div>
                       <div class="col-12" style="margin: 10px 0px;">
                           <div class="">
                               <input :id="'files'+selectedData.cardId" :ref="selectedData.cardId"
                                      style="display: none;" @change="updateCardPicture($event,selectedData)"
                                      type="file">
                               <a @click="addCardAttachment(selectedData)" class="btn btn-default btn-sm"
                                  style="border: 1px solid #f1efe6">
                                       Upload File <i class="fal fa-file-upload"></i>
                               </a>
                           </div>
                       </div>
                   </div>
               </span>
            </div>

            <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="imageModal" role="dialog"
                 tabindex="-1">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Image Show</h5>
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


            <div aria-labelledby="comment-tab" class="tab-pane" id="comment" role="tabpanel" style="overflow: hidden;">
                <span>
                    <div class="row comment-section-in-task-details" style="min-height: calc(100vh - 285px);">
                        <div id='cmntSection'
                             style="margin:0px 28px; max-height: calc(100vh - 370px); width: 100%; overflow: auto;">

                            <div class="comment_block" style="height: calc(100vh - 400px);">
                                <div class="new_comment">
                                    <template v-for="comments in comment" style="margin-top: 15px;">
                                        <ul class="user_comment">
                                            <div class="user_avatar" :title="comments.user.name" data-placement="bottom"
                                                 data-toggle="tooltip">
                                                <img :src="comments.user.photo_url"
                                                     v-if="comments.user.photo_url !== null && comments.user.photo_url !== ''">
                                                <p :title="comments.user.name"
                                                   data-placement="bottom" data-toggle="tooltip"
                                                   class="comment-avature user_avatar"
                                                   v-else>
                                                    {{ comments.user.name.substring(0,2) }}</p>
                                            </div>
                                            <div class="comment_body" style="word-break: break-word;">
                                                <span class="user">{{comments.user.name}}</span>
                                                <span> | {{comments.created_at.substring(11,16)}} | </span>
                                                <span>{{ ' '+comments.created_at.substring(0,10)}}</span>
                                                <span style="padding: 10px;"
                                                      v-if="comments.comment != '' && comments.comment != null">
                                                    <p>
                                                        <span class="commentDetails" v-html="comments.comment"></span>
                                                    </p>
                                                </span>
                                                <span style="padding: 10px;"
                                                      v-if="comments.attatchment != '' && comments.attatchment != null">
                                                    <span class="user">{{comments.user.name}} :</span>
                                                    <a target="_blank"
                                                       :href="'/storage/'+selectedData.cardId+'/comment/'+comments.attatchment"
                                                       style="cursor: pointer;">
                                                        <div
                                                            v-if="comments.attatchment.toLowerCase().endsWith('.png') || comments.attatchment.toLowerCase().endsWith('.jpg') || comments.attatchment.toLowerCase().endsWith('.gif')">
                                                            <img title="Click To Download" data-toggle="tooltip"
                                                                 :src="'/storage/'+selectedData.cardId+'/comment/'+comments.attatchment"
                                                                 height="80" width="80">
                                                        </div>
                                                        <div
                                                            v-else-if="comments.attatchment.toLowerCase().endsWith('.txt') ">
                                                            <img title="Click To Download" data-toggle="tooltip"
                                                                 :src="'/img/txt.png'" height="50" width="50">
                                                        </div>
                                                        <div
                                                            v-else-if="comments.attatchment.toLowerCase().endsWith('.pdf') ">
                                                            <img title="Click To Download" data-toggle="tooltip"
                                                                 :src="'/img/pdf.png'" height="50" width="50">
                                                        </div>
                                                        <div
                                                            v-else-if="comments.attatchment.toLowerCase().endsWith('.doc') || comments.attatchment.toLowerCase().endsWith('.docx') || comments.attatchment.toLowerCase().endsWith('.xls') || comments.attatchment.toLowerCase().endsWith('.xlsx')">
                                                            <img title="Click To Download" data-toggle="tooltip"
                                                                 :src="'/img/file.png'" height="50" width="50">
                                                        </div>
                                                        <div style="float: left;" v-else>
                                                            <img title="Click To Download" data-toggle="tooltip"
                                                                 :src="'/img/attachment.png'" height="50" width="50">
                                                        </div>
                                                    </a>
                                                </span>
                                            </div>

                                            <div class="comment_toolbar">
                                                <div class="comment_details">
                                                    <ul>

                                                        <li @click="showCommentBox(comments.id,comments.comment)"><i
                                                            class="fa fa-pencil"></i>
                                                        <li @click="deleteDetailComment(comments.id)"><i
                                                            class="fal fa-trash-alt"></i></li>
                                                        <li @click="replyToComment(comments.id)"><i
                                                            class="fas fa-reply"></i></li>
                                                    </ul>
                                                </div>

                                            </div>
                                            <li :id="'replyBox'+comments.id" class="replyCommentSection"
                                                style="display : none;">

                                                <div class="mb-3 input-group display-inline position-relative"
                                                     style="margin-bottom: 10px; left: 45px;">
                                                    <div :id="'myUL-user-reply'+comments.id"
                                                         class="myUL-user-comment myUL-user-comment-reply"
                                                         style="left: 0px;">
                                                        <template v-for="user in replyProjectUsers"
                                                                  v-if=" replyProjectUsers !== null && replyProjectUsers.length > 0">
                                                            <li @click="replySearchTaskByAssignedUser(user.id,user.name,comments.id)">
                                                                <a href="#">
                                                                    <span class="assignUser-suggest-photo">
                                                                            {{(user.name !== null) ? user.name.substring(0,2) : ''}}
                                                                    </span> {{user.name}}
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
                                                    </div>
                                                    <div class="replyWrapper"
                                                         style=" width: 100%; margin-right: 136px;">
                                                        <div>
                                                            <textarea :id="'replyTextBox'+comments.id" type="text"
                                                                      class="custom-input"
                                                                      name="subscribe_email"
                                                                      @keyup="commentReplyPress($event,selectedData,comments.id)"
                                                                      placeholder="Reply ... " v-model="editAndReplayText" autocomplete="off">
                                                            </textarea>
                                                        </div>
                                                        <div class="input-group-append">
                                                            <span :id="'replyBtn'+comments.id"
                                                                  @click="CardCommentUpdate(comments.id, selectedData.cardId, 'replay')"
                                                                  class="input-group-text">Reply</span>
                                                            <span :id="'editReplyBtn'+comments.id"
                                                                  @click="CardCommentUpdate(comments.id, selectedData.cardId, 'update')"
                                                                  class="input-group-text" style="display: none">Edit Reply</span>
                                                            <span :id="'editCommentBtn'+comments.id"
                                                                  @click="CardCommentUpdate(comments.id, selectedData.cardId, 'update')"
                                                                  class="input-group-text"
                                                                  style="display: none">Update</span>
                                                        </div>
                                                    </div>
                            </div>

                            </li>
                            <ul style="position: relative; left: 77px; width: calc(100% - 80px);margin-top: 23px;" v-if="comments.comment_reply.length > 0" v-for="reply in comments.comment_reply">
                                <div class="user_avatar" :title="reply.user.name" data-placement="bottom" data-toggle="tooltip">
                                    <img :src="reply.user.photo_url" v-if="reply.user.photo_url !== null && reply.user.photo_url !== ''">
                                    <p :title="reply.user.name" data-placement="bottom" data-toggle="tooltip" class="comment-avature user_avatar" v-else>
                                        {{ reply.user.name.substring(0,2) }}
                                    </p>
                                </div>
                                <div class="comment_body" style="word-break: break-word;">
                                    <span style="" v-if="reply.comment != '' && reply.comment != null">
                                        <p>
                                            <span class="commentReply" v-html="reply.comment"></span>
                                        </p>
                                    </span>
                                    <span style="padding: 10px;" v-if="reply.attatchment != '' && reply.attatchment != null">
                                        <span class="user">{{reply.user.name}} :</span>
                                        <a target="_blank" :href="'/storage/'+selectedData.cardId+'/comment/'+reply.attatchment" style="cursor: pointer;">
                                            <div v-if="reply.attatchment.endsWith('.png') || reply.attatchment.endsWith('.jpg') || reply.attatchment.endsWith('.gif')">
                                                <img title="Click To Download" data-toggle="tooltip" :src="'/storage/'+selectedData.cardId+'/comment/'+reply.attatchment" height="80" width="80">
                                            </div>
                                            <div v-if="reply.attatchment.endsWith('.txt') ">
                                                <img title="Click To Download" data-toggle="tooltip" :src="'/img/txt.png'" height="50" width="50">
                                            </div>
                                            <div v-if="reply.attatchment.endsWith('.pdf') ">
                                                <img title="Click To Download" data-toggle="tooltip" :src="'/img/pdf.png'" height="50" width="50">
                                            </div>
                                            <div v-if="reply.attatchment.endsWith('.doc') || reply.attatchment.endsWith('.docx') || reply.attatchment.endsWith('.xls') || reply.attatchment.endsWith('.xlsx')">
                                                <img title="Click To Download" data-toggle="tooltip"
                                                     :src="'/img/file.png'" height="50" width="50">
                                            </div>
                                        </a>
                                    </span>
                                </div>
                                <div class="comment_toolbar">
                                    <div class="comment_details-reply" style="width: 100% !important;">
                                        <ul>
                                            <li><i class="fa fa-clock-o"></i> {{ reply.created_at.substring(11,16)}}</li>
                                            <li><i class="fa fa-calendar"></i> {{ reply.created_at.substring(0,10)}}</li>
                                            <li @click="showReplyBox(comments.id,reply.id,reply.comment)"><i
                                                class="fa fa-pencil"></i> <span class="user"> {{ reply.user.name }}</span></li>
                                            <li @click="deleteDetailComment(reply.id)"><i
                                                class="fa fa-trash"></i> <span class="user"
                                                                               style="color: red"> Delete</span></li>
                                        </ul>
                                    </div>

                                </div>
                            </ul>
                            </ul>
</template>

</div>
</div>
</div>
<div class="col-12" style="margin: 10px 0px;">

    <div v-click-outside="HideTextArea">
        <ul id="myUL-user" class="myUL-user-comment">
            <template v-for="user in projectUsers" v-if=" projectUsers !== null && projectUsers.length > 0">
                <li @click="SearchTaskByAssignedUser(user.id,user.name, selectedData.cardId)">
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
        <div class="row">
            <div class="col-md-1">
                <p :title="selectedData.userName" class="assignUser-photo-for-selected text-uppercase details-comments-pic"
                   data-placement="bottom" data-toggle="tooltip" style="overflow:hidden; margin-left: 30%;;"> {{                    selectedData.userName.substring(0,2) }}
                </p>
            </div>
            <div class="col-md-11">
                <textarea @focus="ShowTextArea(selectedData)"
                          :id="'comment'+selectedData.cardId"
                          class="form-control commentInput"
                          data-grow="auto"
                          @keyup="commentPress($event,selectedData)"
                          @click="commentEdit(selectedData.cardId)"
                          placeholder="Add comment"
                          v-model="commentText">
                </textarea>
                <div class="SubmitButton" id="SubmitButton" style="margin-bottom: 10px; margin-top: 10px;">
                    <a class="btn btn-default btn-sm" style="background: #7BB348;" @click="CardCommentSubmit()">Post</a>

                    <input :id="'file'+selectedData.cardId" :ref="selectedData.cardId" style="display: none;"
                           @change="updatePicture($event,selectedData)"
                           type="file">
                    <a @click="addAttachment(selectedData)" class="btn btn-default btn-sm"
                       style="border: 1px solid #f1efe6">
                        <i class="fa fa-paperclip"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</span>
</div>
<div aria-labelledby="log-tab" class="tab-pane" id="log" role="tabpanel">
      <span>
          <div class="log-data" style="display: inline !important;">
              <h3 class="p-3">Log data</h3>
              <div class="tags-log">
                  <table class="table table-striped table-bordered">
                      <thead class="save-all">
                          <tr>
                              <th scope="col" style=" width: 35%;font-size: 14px">Title</th>
                              <th scope="col" style=" width: 33%;font-size: 14px">Log Type</th>
                              <th scope="col" style=" width: 16%;font-size: 14px">Action By</th>
                              <th scope="col" style=" width: 17%;font-size: 14px">Action At</th>
                          </tr>
                      </thead>
                      <tbody>
                      <template v-if="task_logs.length > 0">
                          <tr v-for="log in task_logs">
                              <td><div v-html="log.title"></div></td>
                              <td><div v-html="log.log_type"></div></td>
                              <td>{{log.user.name}}</td>
                              <td class="text-center">{{ log.action_at | relative }} </td>
                          </tr>
                      </template>
                      <template v-else>
                          <tr>
                              <td colspan="4" align="center">Log data not yet.</td>
                          </tr>
                      </template>
                      </tbody>
                  </table>
              </div>
          </div>
      </span>
</div>
<div aria-labelledby="child-tab" class="tab-pane" id="child" role="tabpanel">
    <span>
        <div class="tags-log">
            <div v-if="selectedData.childrens.length  > 0">
                <label class="label"> <h5>Tree View:</h5></label>
                <div v-for="(child,index) in selectedData.childrens">
                    <li class="list-group-item detail-tree-view ">
                        <label class="checkbox_cus_mini ">
                            {{ child.title }}
                        </label>
                        <ul v-if="child.child_task.length  > 0" class="list-group list-group-flush">
                            <div v-for="child1 in child.child_task">
                                <li class="list-group-item detail-tree-view">
                                    <label class="checkbox_cus_mini ">
                                        {{ child1.title }}
                                        </label>
                                    <ul v-if="child1.child_task.length  > 0"
                                        class="list-group list-group-flush">
                                        <div v-for="child2 in child1.child_task">
                                            <li class="list-group-item detail-tree-view">
                                                <label class="checkbox_cus_mini ">
                                                    {{ child2.title }}
                                                </label>
                                                <ul v-if="child2.child_task.length  > 0"
                                                    class="list-group list-group-flush">
                                                    <div v-for="child3 in child2.child_task">
                                                        <li class="list-group-item detail-tree-view">
                                                            <label class="checkbox_cus_mini ">
                                                                {{ child3.title }}
                                                            </label>
                                                            <ul v-if="child1.child_task.length  > 0"
                                                                class="list-group list-group-flush">
                                                                <div v-for="child2 in child1.child_task">
                                                                    <li class="list-group-item detail-tree-view">
                                                                        <label class="checkbox_cus_mini ">
                                                                            {{ child2.title }}
                                                                        </label>
                                                                    </li>
                                                                </div>
                                                            </ul>
                                                        </li>
                                                    </div>
                                                </ul>
                                            </li>
                                        </div>
                                    </ul>
                                </li>
                            </div>
                        </ul>
                    </li>
                </div>
            </div>
        </div>
    </span>
</div>
</div>
</div>
</template>

<style scoped>
    .detail-tree-view {
        border-top: 1px solid gray !important;
    }

    .selected-li {
        background-color: #ddf3fd;
    }
</style>
<script>
    import switches from 'vue-switches';
    import Datepicker from 'vuejs-datepicker';
    import VueTagsInput from '@johmun/vue-tags-input';
    import ClickOutside from 'vue-click-outside';
    import CKEditor from '@ckeditor/ckeditor5-vue';
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import Swal from 'sweetalert2';
    import Helper from '../../helper';

    export default {
        components: {switches, Datepicker, VueTagsInput, ckeditor: CKEditor.component},
        name: "TaskDetails",
        data() {
            return {
                comment: null,
                editor: ClassicEditor,
                editorData: '<p>Content of the editor.</p>',
                editorConfig: {
                    // The configuration of the editor.
                },
                showDetails: false,
                disabledDates: {
                    id: null
                },
                id: 0,
                tags: [],
                cards: [],

                baseUrl: window.location.origin,
                addField: {
                    name: null,
                    color: null,
                    error: null,
                    progress: null
                },
                editField: {
                    name: null,
                    color: null,
                    boardId: null,
                    error: null,
                    progress: null
                },
                date_config: {
                    enableTime: false,
                    wrap: true,
                    disableMobile: true,
                    altFormat: 'Y-m-d',
                    dateFormat: 'd M',
                },
                nav: [],
                board: [],
                subNav: [],
                subBoard: [],
                boardColumn: [],
                selectedBoard: 'Select Board',
                selectedNav: 'Select Nav',
                selectedSubNav: 'Select Nav List',
                selectedSubBoard: 'Select Board List',
                selectedBoardColumn: 'Select Board Column',
                transferBtn: false,
                project: null,
                projectUsers: null,
                replyProjectUsers: null,
                tree4data: [],
                currentColumn: null,
                currentColumnIndex: null,
                scene: {},
                mentionUsers: [],
                upperDropPlaceholderOptions: {
                    className: 'cards-drop-preview',
                    animationDuration: '150',
                    showOnTop: true
                },
                dropPlaceholderOptions: {
                    className: 'drop-preview',
                    animationDuration: '150',
                    showOnTop: true
                },
                updateIndex: null,
                tag: '',
                tag1: [],
                selectedExistedTask: [],
                multiple_list: null,
                list: {
                    name: null,
                    description: null,
                    nav_id: null
                },
                navItem: {
                    title: null,
                    type: null,
                    sort_id: null,
                    project_id: null,
                },
                task_logs: [],
                check_uncheck_child: null,
                manageTag: null,
                replyId: null,
                trigger: false,
                userNames: '',
                commentsData: '',
                Socket: null,
                editors: null,
                selectedData: {},
                authUser: {
                    name : '',
                    email : ''
                },
                commentText: null,
                cmntEditors: null,
                editAndReplay: null,
                editAndReplayText: null,
                searchTag: null,
                modalImg : ['',''],
                userSeggistion: null,
                allTeamsUsers: [],
            }
        },
        mounted() {
            let _this = this;
            $('#loder-hide').removeClass('loder-hide');
            _this.selectedData = {
                id: null,
                parent_id: 0,
                sort_id: 0,
                board_parent_id: null,
                multiple_board_id: null,
                priority_label: null,
                list_id: null,
                text: '',
                draggable: true,
                droppable: true,
                clicked: 0,
                count_child: 0,
                date: "0000-00-00",
                progress: null,
                open: 1,
                tags: [],
                tagTooltip: "",
                complete_tooltip: "",
                description: "",
                files: [],
                assigned_user: [],
                assigned_user_ids: [],
                users: [],
                existing_tags: [],
                comment: [],
                children: [],
                active: false,
                style: {},
                class: "",
                innerStyle: {},
                innerClass: "",
                innerBackStyle: {},
                innerBackClass: {},
                parent: {},
                childrens: [],
                child: [],
                parents: [],
                userName: '',
                cardId: '',
                data: "",
                type: "",
                created_by : {
                    name : ''
                }
            }

            _this.$toastr.defaultTimeout = 1500;
            setTimeout(function () {
                CKEDITOR.plugins.get('bbcode');
                let token = Spark.csrfToken; //_this.getMeta('csrf-token');
                _this.editors = CKEDITOR.replace('editor', {
                    filebrowserUploadUrl: '/api/card-file-upload?type=json',
                    filebrowserUploadMethod: 'xhr',
                    fileTools_requestHeaders: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token
                    }
                });
                _this.editors.on('fileUploadResponse', function (evt) {
                    // Prevent the default response handler.
                    evt.stop();
                    // Get XHR and response.
                    var data = evt.data,
                        xhr = data.fileLoader.xhr,
                        response = xhr.responseText.split('|');
                    if (response[1]) {
                        // An error occurred during upload.
                        data.message = response[1];
                        evt.cancel();
                    } else {
                        data.url = response[0];
                    }
                });
                _this.onBlurDetails();
            }, 500)

            setTimeout(function () {
                $('[data-toggle="tooltip"]').tooltip();
                var hei = $("#cmntSection").height();
                $("#cmntSection").animate({scrollTop: hei}, 2000);
            }, 1000);
            _this.onPasteFile();
            _this.connectSocket();
            setTimeout(function () {
                _this.getAuthUser();
                _this.getTask();
                _this.selectedData.text == '' ? _this.getTask() : false;
            }, 1500);
        },
        created() {
            $('#hide-single-view').hide()
        },
        methods: {
            getAuthUser() {
                // auth-user
                var _this = this;
                axios.get('/api/auth-user')
                    .then(response => response.data)
                    .then(response => {
                        _this.authUser = response.user;
                    })
                    .catch(error => {

                    });
            },
            getTask() {
                let _this = this;
                var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                axios.post('/api/single-task', {tz: tz, project_id : _this.$route.params.projectId, id : atob(_this.$route.params.task_id)})
                    .then(response => response.data)
                    .then(response => {
                        var data = response.task;
                        _this.selectedData = data;
                        _this.selectedData.childrens = response.child;
                        _this.selectedData.data = _this.selectedData.text;
                        _this.selectedData.users = response.users;
                        _this.selectedData.child = [];
                        _this.selectedData.parents = response.parents;
                        _this.selectedData.userName = _this.authUser.name;
                        _this.selectedData.cardId = _this.selectedData.id;
                        _this.selectedData.type = 'task';
                        _this.selectedData.allTags = response.allTags;

                        $('#loder-hide').fadeOut();
                        $('#hide-single-view').show()
                        let desc = data.description == null ? "  " : data.description
                        _this.editors.setData(desc);
                        var task_view_type = JSON.parse(localStorage.task_view_type);
                        if (task_view_type == 'comment') {
                            _this.getComments(_this.$route.params.task_id);
                            localStorage.task_view_type = null;
                            $('#comment-nav').click();
                            $('.nav-tabs a[href="#comment"]').tab('show');
                        }
                    })
                    .catch(error => {
                        console.log(error)
                        console.log('Api for get single task not Working !!!')
                    });
            },
            getMeta(metaName) {
                const metas = document.getElementsByTagName('meta');

                for (let i = 0; i < metas.length; i++) {
                    if (metas[i].getAttribute('name') === metaName) {
                        return metas[i].getAttribute('content');
                    }
                }

                return '';
            },
            connectSocket: function () {
                let app = this;
                if (app.Socket == null) {
                    app.Socket = io.connect(window.socket_url);
                    app.Socket.on('listUpdateSocket', function (res) {
                        if (res.task_id != undefined && res.task_id == app.selectedData.id) {
                            app.getComments();
                            app.getFiles();
                        }
                    })

                    app.Socket.on('taskUpdateSocket', function (res) {
                        if (res.task_id == app.selectedData.id) {
                            app.getTask();
                        }
                    })
                }
            },
            handlePaste() {
                console.log('paste');
            },
            grow: function (text, options) {
                var height = options.height || '100px';
                var maxHeight = options.maxHeight || '500px';
                text.style.height = 'auto';
                var curHeight = text.scrollHeight;
                if (curHeight > maxHeight) {
                    curHeight = maxHeight;
                    text.style.overflow = 'auto';
                } else {
                    text.style.overflow = 'hidden';
                }
                if (curHeight < height) {
                    curHeight = height;
                }
                text.style.height = curHeight + 'px';
            },
            growInit: function (options) {
                let _this = this;
                var locInputs = document.querySelectorAll('[data-grow="auto"]');
                for (var i = 0; i < locInputs.length; i++) {
                    var target = locInputs[i];
                    var height = options.height || '100px';
                    var maxHeight = options.maxHeight || '500px';
                    target.style.overflow = 'hidden';
                    target.style.resize = 'none';
                    target.style.height = height + 'px';
                    target.style.maxHeight = maxHeight + 'px';

                    target.onkeydown = function () {
                        _this.grow(this, options);
                    };
                    target.onkeyup = function () {
                        _this.grow(this, options);
                    };
                }
            },
            updateDate(date) {
                date = new Date(date);
                var _this = this;
                var month = (parseFloat(date.getMonth() + 1) > 9) ? parseFloat(date.getMonth() + 1) : '0' + parseFloat(date.getMonth() + 1);
                var formatedDate = date.getFullYear() + '-' + month + '-' + date.getDate();

                var postData = {
                    id: _this.selectedData.cardId,
                    date: date
                };
                axios.post('/api/card-update/' + _this.selectedData.cardId, postData)
                    .then(response => response.data)
                    .then(response => {
                        Bus.$emit('taskUpdate', {
                            list_id: _this.selectedData.list_id
                        })
                        _this.Socket.emit('taskUpdate', {
                            project_id: _this.selectedData.project_id,
                            list_id: _this.selectedData.list_id,
                            board_id: _this.selectedData.multiple_board_id,
                            user_id: _this.authUser.id
                        })
                        _this.getTask();
                    })
                    .catch(error => {
                        console.log('Api for task date update not Working !!!')
                    });
            },
            HideListDetails() {
                var _this = this;
            },
            HideDetails() {
                $('#task_width').addClass('task_width').removeClass('task_widthNormal');
                $('#details').addClass('details').removeClass('detailsShow');
            },
            onBlurDetails() {
                let _this = this;
                _this.editors.setData(_this.selectedData.description);
                _this.editors.on('blur', function (evt) {
                    let datas = evt.editor.getData();
                    if (_this.selectedData.description !== datas) {
                        _this.updateDescription('#bx' + _this.selectedData.cardId, datas);
                    }
                });
                $('#cke_1_contents').on('paste', function (event) {
                    return false;
                    var items = (event.clipboardData || event.originalEvent.clipboardData).items;
                    var blob = null;
                    for (var i = 0; i < items.length; i++) {
                        if (items[i].type.indexOf("image") === 0) {
                            blob = items[i].getAsFile();
                        }
                    }

                    if (blob !== null) {
                        var reader = new FileReader();
                        reader.onload = function (event) {
                            var ImageURL = event.target.result;
                            var block = ImageURL.split(";");
                            var contentType = block[0].split(":")[1];
                            var realData = block[1].split(",")[1];
                            var blob = _this.b64toBlob(realData, contentType);
                            var formData = new FormData();
                            formData.append('file', blob);
                            formData.append('id', _this.selectedData.cardId);
                        };
                        reader.readAsDataURL(blob);
                    }
                });
            },
            updateButtonDescription(){
                let _this = this;
            },
            updateDescription(id, data) {

                var _this = this;
                _this.selectedData.description = data;
                var postData = {
                    id: _this.selectedData.cardId,
                    description: data
                };
                axios.post('/api/card-update/' + _this.selectedData.cardId, postData)
                    .then(response => response.data)
                    .then(response => {
                        Bus.$emit('detailsUpdate', {
                            data: response.data
                        })
                        _this.HideDetails();
                        Bus.$emit('taskUpdate', {
                            list_id: _this.selectedData.list_id
                        })
                        _this.Socket.emit('taskUpdate', {
                            project_id: _this.selectedData.project_id,
                            list_id: _this.selectedData.list_id,
                            board_id: _this.selectedData.multiple_board_id,
                            user_id: _this.authUser.id
                        })
                    })
                    .catch(error => {
                        console.log('Api for move down task not Working !!!');
                    });
            },
            HideTextArea() {
                var _this = this;
                _this.projectUsers = null;
                $('.myUL-user-comment').css({display: 'none'});
                $('#cmntSection').css({maxHeight: ' calc(100vh - 370px)'});
            },
            ShowTextArea(data) {
                $('#cmntSection').css({maxHeight: ' calc(100vh - 420px)'});
                this.$emit('textArea', data)
                $('#comment' + data.cardId).css({height: '50px', maxHeight: '100px'});
                $('.myUL-user-comment').css({bottom: '108px'});
                var hei = $("#cmntSection").height();
                $("#cmntSection").animate({scrollTop: hei}, 1000);
                // var _this = this;
                $('.SubmitButton').show();
            },
            ShowListDetails(data) {
                var _this = this;
                $('.submitdetails').show();
                var option = {
                    height: 50,
                    maxHeight: 400
                };
                _this.growInit(option);
            },
            addAttachment(data) {
                let refData = data.cardId;
                $('#file' + refData).click();
            },
            addCardAttachment(data) {
                let refData = data.cardId;
                $('#files' + refData).click();
            },
            updatePicture(e, data) {
                var _this = this;
                this.file = e.target.files[0];
                let formData = new FormData();
                formData.append('file', this.file);
                formData.append('id', data.cardId);
                formData.append('files', 'sdsds');

                axios.post('/api/comment-file-upload', formData, {headers: {'Content-Type': 'multipart/form-data'}})
                    .then(response => response.data)
                    .then(response => {
                        _this.comment.push(response.Data);
                        setTimeout(() => {
                            $('[data-toggle="tooltip"]').tooltip();
                        }, 100);
                        Bus.$emit('taskUpdate', {
                            list_id: _this.selectedData.list_id
                        })
                        _this.Socket.emit('taskUpdate', {
                            project_id: _this.selectedData.project_id,
                            list_id: _this.selectedData.list_id,
                            board_id: _this.selectedData.multiple_board_id,
                            user_id: _this.authUser.id,
                            task_id : _this.selectedData.id
                        })
                    })
                    .catch(error => {
                        console.log('Api for task date update not Working !!!')
                    });
            },
            updateCardPicture(e, data) {
                var _this = this;
                this.file = e.target.files[0];
                let formData = new FormData();
                formData.append('file', this.file);
                formData.append('id', data.cardId);
                formData.append('files', 'sdsds');

                axios.post('/api/card-file-upload', formData, {headers: {'Content-Type': 'multipart/form-data'}})
                    .then(response => response.data)
                    .then(response => {
                        console.log(response.files);
                        console.log(_this.selectedData.files);
                        _this.selectedData.files.push(response.files);
                        console.log(_this.selectedData.files);
                        setTimeout(() => {
                            $('[data-toggle="tooltip"]').tooltip();
                            e.target.type = 'text';
                            e.target.value = '';
                            e.target.type = 'file';
                        }, 100);
                        Bus.$emit('taskUpdate', {
                            list_id: _this.selectedData.list_id
                        })
                        _this.Socket.emit('taskUpdate', {
                            project_id: _this.selectedData.project_id,
                            list_id: _this.selectedData.list_id,
                            board_id: _this.selectedData.multiple_board_id,
                            user_id: _this.authUser.id,
                            task_id : _this.selectedData.id
                        })
                    })
                    .catch(error => {
                        console.log('Api for task date update not Working !!!')
                    });
            },
            deleteFile(id) {
                let _this = this;

                Swal.fire(
                    Helper.swalDelete('Are you sure to delete this file?', '')
                ).then((result) => {
                    if (result.value) {
                        var data = {
                            'id': id
                        };
                        axios.post('/api/delete-card-file', data)
                            .then(response => response.data)
                            .then(response => {
                                _this.getFiles(_this.selectedData.cardId);
                                Swal.fire("Deleted!", "Successfully Deleted", "success");
                                setTimeout(() => {
                                    Swal.close();
                                }, 1000);

                                Bus.$emit('taskUpdate', {
                                    list_id: _this.selectedData.list_id
                                })
                                _this.Socket.emit('taskUpdate', {
                                    project_id: _this.selectedData.project_id,
                                    list_id: _this.selectedData.list_id,
                                    board_id: _this.selectedData.multiple_board_id,
                                    user_id: _this.authUser.id,
                                    task_id : _this.selectedData.id
                                })
                            })
                            .catch(error => {

                            });
                    }
                })

            },

            CardCommentSubmit(){
                let _this = this;
                let datas = _this.cmntEditors.getData();

                console.log(datas);
                if (_this.commentText !== datas) {
                    _this.saveComment(_this.selectedData.cardId, datas);
                    _this.commentText = "";
                    _this.cmntEditors.setData('');
                }
            },

            saveComment(id, data) {
                let _this = this;
                //let comment = $('#comment' + id).val();
                let comment = data;
                if (comment === '' || comment === null) {
                    Swal.fire('Warning!!', 'Comment is empty', 'warning');
                    setTimeout(() => {
                        Swal.close();
                    }, 1000);
                    return false;
                }
                let mailUsers = [];
                for (let index = 0; index < _this.mentionUsers.length; index++) {
                    if (comment.includes('@' + _this.mentionUsers[index].name)) {
                        mailUsers.push(_this.mentionUsers[index].id);
                    }
                }
                _this.mentionUsers = [];
                var commentData = {
                    'comment': comment,
                    'task_id': id,
                    'mailUsers': mailUsers,
                };
                axios.post('/api/add-comment', commentData)
                    .then(response => response.data)
                    .then(response => {

                        $('#comment' + id).val('');

                        setTimeout(() => {
                            _this.comment.push(response.Data);
                            _this.HideTextArea();
                            var hei = $("#cmntSection").height();
                            $("#cmntSection").animate({scrollTop: hei}, 1000);
                            _this.$toastr.s('Comment Create Success !');
                            if (response.users.length > 0) {
                                _this.Socket.emit('notification-update', response.users);
                                Bus.$emit('UpdateListOrBoard')
                                _this.Socket.emit('taskUpdate', {
                                    project_id: _this.selectedData.project_id,
                                    list_id: _this.selectedData.list_id ,
                                    board_id: _this.selectedData.multiple_board_id,
                                    user_id: _this.selectedData.id
                                })
                            }
                            _this.Socket.emit('updateTask-overview', {
                                project_id: _this.selectedData.project_id,
                                type : 'comments'
                            })
                        }, 1000);
                    })
                    .catch(error => {

                    });

            },
            showImage(image, task_id) {
                this.modalImg = [image, task_id];
                $("#imageModal").modal();
            },
            showAssignedUserRemoveButton(data) {

                $('[data-toggle="tooltip"]').tooltip('hide');

                setTimeout(function () {
                    $('#remove-assign-user-modal' + data.cardId).toggleClass('remove-assign-user');
                    $('#remove-assign-user-modal' + data.cardId).toggle();
                    $('#remove-assign-user-modal' + data.cardId).removeClass('remove-assigned');
                }, 500)

            },
            commentPress(e, cardId, data) {
                let _this = this;
                let title = data; //$('#comment' + data.cardId).val();
                let cmHe = $('#comment' + cardId).height();
                $('#cmntSection').css({maxHeight: ' calc(100vh - 420px - ' + cmHe + 'px + 30px)'});
                $('.myUL-user-comment').css({bottom: ' calc(100vh - 864px + ' + cmHe + 'px - 38px)'});
                if (e.which === 32 || e.which === 13) {
                    _this.trigger = false;
                    _this.userNames = '';
                    _this.projectUsers = null;
                }
                if (_this.trigger == true && e.which !== 16 && e.which !== 50) {
                    setTimeout(() => {
                        var lastIndex = title.lastIndexOf(" ");
                        let str = title.substring(lastIndex);
                        if (str.includes('@')) {
                            let notKeys = ["Backspace", "ScrollLock", "null", "NumLock", "Tab", "ArrowLeft", "ArrowDown", "ArrowRight", "ArrowUp", "Control"];
                            if (notKeys.includes(e.key) === false) {
                                _this.userNames += e.key;
                            }
                            if (e.key === "Backspace") {
                                _this.userNames = _this.userNames.slice(0, -1);
                            }
                            axios.post('/api/task-list/search-result', {'user_name': _this.userNames})
                                .then(response => response.data)
                                .then(response => {
                                    _this.projectUsers = response.search_user;
                                    $('.myUL-user-comment').css({display: 'none'});
                                    if (_this.projectUsers.length > 0) {
                                        $('.myUL-user-comment').css({display: 'inline-block', bottom: '108px'});
                                    }
                                })
                                .catch(error => {
                                    console.log('search user is not Working !!!')
                                });
                        }
                    }, 100);
                }

                if ((e.shiftKey && e.which == 50) || e.key === '@') {
                    _this.trigger = true;
                    _this.commentsData = $('.commentInput').val();
                    axios.get('/api/task-list/all-suggest-user')
                        .then(response => response.data)
                        .then(response => {
                            $('.myUL-user-comment').css({display: 'inline-block', bottom: '108px'});
                            _this.projectUsers = response.search_user;
                        })
                        .catch(error => {
                            console.log('All suggest user api not working')
                        })
                }
            },
            commentsReplyKeyPress(e) {
                if (e.which === 32 || e.which === 13) {
                }
            },
            commentReplyPress(e, dataId, comments) {
                let _this = this;
                let title = comments; //$('#replyTextBox' + comments.id).val();
                if (e.which === 32 || e.which === 13) {
                    _this.trigger = false;
                    _this.userNames = '';
                    _this.replyProjectUsers = null;
                    $('.myUL-user-comment').css({display: 'none'});
                }
                if (_this.trigger == true && e.which !== 16 && e.which !== 50) {
                    var lastIndex = title.lastIndexOf(" ");
                    let str = title.substring(lastIndex);
                    if (str.includes('@')) {
                        let notKeys = ["Backspace", "ScrollLock", "null", "NumLock", "Tab", "ArrowLeft", "ArrowDown", "ArrowRight", "ArrowUp"];
                        if (notKeys.includes(e.key) === false) {
                            _this.userNames += e.key;
                        }
                        if (e.key === "Backspace") {
                            _this.userNames = _this.userNames.slice(0, -1);
                        }
                        axios.post('/api/task-list/search-result', {'user_name': _this.userNames})
                            .then(response => response.data)
                            .then(response => {
                                _this.replyProjectUsers = response.search_user;
                                $('.myUL-user-comment').css({display: 'none'});
                                if (_this.replyProjectUsers.length > 0) {
                                    $('.myUL-user-comment').css({display: 'block'});
                                }
                            })
                            .catch(error => {
                                console.log('search user is not Working !!!')
                            });
                    }
                }

                if (e.shiftKey && e.which == 50) {
                    _this.trigger = true;
                    _this.commentsData = comments; //$('#replyTextBox' + comments.id).val();
                    axios.get('/api/task-list/all-suggest-user')
                        .then(response => response.data)
                        .then(response => {
                            $('.myUL-user-comment').css({display: 'inline-block', bottom: '108px'});
                            _this.replyProjectUsers = response.search_user;
                        })
                        .catch(error => {
                            console.log('All suggest user api not working')
                        })
                }
            },
            userList(e, data) {
                let _this = this;
                var value = e.target.value;
                let cmHe = $('#comment' + data.cardId).height();
                $('#cmntSection').css({maxHeight: ' calc(100vh - 420px - ' + cmHe + 'px + 30px)'});
            },
            replySearchTaskByAssignedUser(id, name, elId) {
                let _this = this;
                _this.mentionUsers.push({id: id, name: name});
                CKEDITOR.instances['replyTextBox'+elId].insertText(name);
                setTimeout(() => {
                    _this.replyProjectUsers = null;
                }, 10);
            },
            SearchTaskByAssignedUser(id, name, cardId) {
                let _this = this;
                _this.mentionUsers.push({id: id, name: name});
                CKEDITOR.instances['comment'+cardId].insertText(name);
                setTimeout(() => {
                    _this.projectUsers = null;
                }, 10);
            },

            removeAssignedUser(user_id, task_id) {

                var _this = this;
                var postData = {
                    user_id: user_id,
                    task_id: task_id
                };
                axios.post('/api/task-list/assign-user-remove', postData)
                    .then(response => response.data)
                    .then(response => {
                        if (response === 'success') {
                            _this.getTask();
                            setTimeout(function () {
                                _this.selectedData.assigned_user = [];
                                _this.selectedData.assigned_user_ids = [];
                            }, 100);
                            Bus.$emit('taskUpdate', {
                                list_id: _this.selectedData.list_id
                            })
                            _this.Socket.emit('taskUpdate', {
                                project_id: _this.selectedData.project_id,
                                list_id: _this.selectedData.list_id,
                                board_id: _this.selectedData.multiple_board_id,
                                user_id: _this.authUser.id,
                                task_id : _this.selectedData.id
                            })
                        }
                    })
                    .catch(error => {
                        console.log('Api assign-user-remove is not Working !!!')
                    });
            },
            assignUserToTask(user, data) {
                var _this = this;
                var postData = {
                    task_id: data.cardId,
                    user_id: user.id
                };
                axios.post('/api/task-list/assign-user', postData)
                    .then(response => response.data)
                    .then(response => {
                        if (response.success === 'success') {
                            _this.getTask();
                            setTimeout(function () {
                                _this.selectedData.assigned_user.push(response.data);
                                _this.selectedData.assigned_user_ids.push(response.data.id);
                            }, 100);
                            Bus.$emit('taskUpdate', {
                                list_id: _this.selectedData.list_id
                            })
                            _this.Socket.emit('taskUpdate', {
                                project_id: _this.selectedData.project_id,
                                list_id: _this.selectedData.list_id,
                                board_id: _this.selectedData.multiple_board_id,
                                user_id: _this.authUser.id,
                                task_id : _this.selectedData.id
                            })
                        }
                    })
                    .catch(error => {
                        console.log('Api is not Working !!!')
                    });
            },
            switchEvent(e) {
                $(e.target).closest('.eachItemRow').find('.switchToggle').collapse('toggle');
            },
            deleteCardTag(obj, card) {
                var _this = this;
                var postData = {
                    assign_id: obj.tag.assign_id,
                };
                if (obj.tag.text !== 'Dont Forget') {
                    axios.post('/api/task-list/delete-tag', postData)
                        .then(response => response.data)
                        .then(response => {
                            _this.selectedData.tags.splice(obj.index, 1);
                            setTimeout(function () {
                            }, 100);
                            _this.tags = [];
                            Bus.$emit('taskUpdate', {
                                list_id: _this.selectedData.list_id
                            })
                            _this.Socket.emit('taskUpdate', {
                                project_id: _this.selectedData.project_id,
                                list_id: _this.selectedData.list_id,
                                board_id: _this.selectedData.multiple_board_id,
                                user_id: _this.authUser.id,
                                task_id : _this.selectedData.id
                            })
                        })
                        .catch(error => {
                            console.log('Api for delete tag not Working !!!')
                        });
                }

            },
            changeTag(tags, card, columnIndex, cardIndex) {
                var _this = this;
                var old = this.selectedData.tags.length;
                var newl = tags.length;
                let cardTags = null;
                if (newl > old) {
                    this.tags = tags;

                    var color = (this.tags[newl - 1].text === 'Dont Forget') ? '#ff0000' : _this.$helpers.generateColor();
                    var postData = {
                        id: card.cardId,
                        tags: _this.tags[newl - 1].text,
                        color: color,
                        type: 'task',
                    };
                    axios.post('/api/task-list/add-tag', postData)
                        .then(response => response.data)
                        .then(response => {

                            setTimeout(function () {
                                _this.selectedData.tags.push({
                                    assign_id: response.data.assign_id,
                                    board_id: response.data.board_id,
                                    classes: "",
                                    color: response.data.color,
                                    id: response.data.id,
                                    style: 'style="background: "' + response.data.color,
                                    text: response.data.title,
                                });
                                $('.dropdown-menu').removeClass('show');
                            }, 100);
                        })
                        .catch(error => {
                            console.log("2nd error =>" + error)
                        });
                }
            },
            addExistingTag(index, cardId, dntfrgt = '') {
                let _this = this;
                if (dntfrgt !== '') {
                    var postData = {
                        id: cardId,
                        tags: "Dont Forget",
                        color: "#FF0000",
                        type: 'task',
                    };
                } else {
                    var postData = {
                        id: cardId,
                        tags: _this.selectedData.allTags[index].title,
                        color: _this.selectedData.allTags[index].color,
                        type: 'task',
                    };
                }

                axios.post('/api/task-list/add-tag', postData)
                    .then(response => response.data)
                    .then(response => {
                        _this.selectedData.tags.push({
                            assign_id: response.data.assign_id,
                            board_id: response.data.board_id,
                            classes: "",
                            color: _this.selectedData.allTags[index].color,
                            id: _this.selectedData.allTags[index].id,
                            style: '',
                            text: _this.selectedData.allTags[index].title,


                            id: _this.selectedData.allTags[index]
                        });
                        _this.selectedData.allTags.splice(index, 1);
                        Bus.$emit('taskUpdate', {
                            list_id: _this.selectedData.list_id
                        })
                        _this.Socket.emit('taskUpdate', {
                            project_id: _this.selectedData.project_id,
                            list_id: _this.selectedData.list_id,
                            board_id: _this.selectedData.multiple_board_id,
                            user_id: _this.authUser.id,
                            task_id : _this.selectedData.id
                        })
                        setTimeout(function () {
                        }, 100);
                    })
                    .catch(error => {
                        console.log("1st error =>" + error)
                    });
            },
            showOriginalList(task) {
                if (task.type !== 'task') {
                    $('#list' + task.list_id).click();
                    setTimeout(function () {
                        $('#click' + task.cardId).click();
                    }, 1000);
                    $('[data-toggle="tooltip"]').tooltip('dispose');
                    setTimeout(function () {
                        $('[data-toggle="tooltip"]').tooltip('enable');
                    }, 500);
                } else {
                    $('.board' + task.multiple_board_id).click();
                    setTimeout(function () {
                        $('#card_' + task.cardId).click();
                    }, 1000);
                    $('[data-toggle="tooltip"]').tooltip('dispose');
                    setTimeout(function () {
                        $('[data-toggle="tooltip"]').tooltip('enable');
                    }, 500);
                }

            },
            showTagManageModel() {
                var _this = this;
                axios.get('/api/task-list/all-tag-for-manage')
                    .then(response => response.data)
                    .then(response => {
                        _this.manageTag = response.tags;
                        $('#TagManage').modal('show');
                    })
                    .catch(error => {
                        console.log('Api for move down task not Working !!!')
                    });

            },

            showChild(cardId) {
                let _this = this;
                let data = {
                    'task_id': cardId
                };
                axios.post('/api/show-child-parent', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.selectedData.childrens = response.childs;//.child_task;
                        _this.selectedData.parents = response.childs;
                        console.log(response.childs.length);
                    })
                    .catch(error => {

                    })
            },
            getFiles(cardId) {
                let _this = this;
                let data = {
                    'task_id': cardId
                };
                _this.selectedData.files = [];
                axios.post('/api/get-card-file', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.selectedData.files = response.files;
                        console.log(_this.selectedData);
                    })
                    .catch(error => {

                    })
            },
            getComments(cardId) {
                let _this = this;
                let data = {
                    'task_id': cardId
                };
                _this.comment = [];
                axios.post('/api/get-card-comment', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.comment = response.comment.comment;
                        _this.selectedData.comment = _this.comment;
                    })
                    .catch(error => {

                    })
            },
            deleteDetailComment(id) {
                let _this = this;

                Swal.fire(
                    Helper.swalDelete('Are you sure to delete this comment?', '')
                ).then((result) => {
                    if (result.value) {
                        var data = {
                            'id': id
                        };
                        axios.post('/api/delete-card-comment', data)
                            .then(response => response.data)
                            .then(response => {
                                _this.getComments(_this.selectedData.cardId);
                                Swal.fire("Deleted!", "Successfully Deleted", "success");
                                setTimeout(() => {
                                    Swal.close();
                                }, 1000);
                            })
                            .catch(error => {

                            });
                    }
                })
            },
            showCommentBox(id, comment) {

                this.replyProjectUsers = null;
                this.replyId = id;
                $('.replyCommentSection').hide();
                $('#editReplyBtn' + id).hide();
                $('.replyWrapper').css('margin-right', '146px');
                $('#editCommentBtn' + id).show();
                $('#replyBox' + id).show();
                $('#replyTextBox' + id).val(comment)
                $('#replyTextBox' + id).focus();
                this.commentEdit2(id);
            },
            replyToComment(id) {
                this.replyProjectUsers = null;
                this.replyId = null;
                $('.replyCommentSection').hide();
                $('#editReplyBtn' + id).hide();
                $('#editCommentBtn' + id).hide();
                $('.replyWrapper').css('margin-right', '136px');
                $('#replyBtn' + id).show();
                $('#replyBox' + id).show();
                $('#replyTextBox' + id).val(' ')
                $('#replyTextBox' + id).focus();
                this.commentEdit2(id);
            },
            showReplyBox(commentId, replyId, replyText) {
                this.replyProjectUsers = null;
                this.replyId = replyId;
                $('.replyCommentSection').hide();
                $('.replyWrapper').css('margin-right', '163px');
                $('#replyBtn' + commentId).hide();
                $('#editCommentBtn' + commentId).hide();
                $('#editReplyBtn' + commentId).show();
                $('#replyBox' + commentId).show();
                $('#replyTextBox' + commentId).val(replyText);
                $('#replyTextBox' + commentId).focus();
                this.commentEdit2(commentId);
            },
            hidereplaybox(id) {
                let _this = this;
                if (_this.replyProjectUsers === null) {
                    $('#replyBox' + id).hide();
                    $('#replyTextBox' + id).val('');
                }
            },

            CardCommentUpdate(id, task_id, type){
                let _this = this;
                let datas = _this.editAndReplay.getData();

                if (_this.editAndReplayText !== datas) {
                    if(type === 'replay'){
                        _this.saveReply(id, task_id, datas);
                    }else{
                        _this.editReply(id, task_id, datas);
                    }
                    _this.editAndReplayText = "";
                    _this.editAndReplay.setData('');
                }
            },

            saveReply(id, task_id, replayData) {
                let _this = this;
                let reply = replayData;
                let mailUsers = [];
                for (let index = 0; index < _this.mentionUsers.length; index++) {
                    if (reply.includes('@' + _this.mentionUsers[index].name)) {
                        mailUsers.push(_this.mentionUsers[index].id);
                    }
                }
                _this.mentionUsers = [];

                let data = {
                    'parent_id': id,
                    'task_id': task_id,
                    'comment': reply,
                    'mailUsers': mailUsers,
                }

                axios.post('/api/save-comment-reply', data)
                    .then(response => data)
                    .then(response => {
                        _this.getComments(task_id);
                        $('#replyBox' + id).hide();
                        _this.Socket.emit('notification-update', response.users);
                    })
                    .catch(error => {

                    })
            },
            editReply(id, task_id, replyData) {
                let _this = this;
                let reply = replyData;
                let data = {
                    'comment': reply,
                    'id': ''
                }
                if (this.replyId !== null) {
                    data.id = this.replyId;
                    this.replyId = null;
                }
                axios.post('/api/update-comment', data)
                    .then(response => data)
                    .then(response => {
                        _this.getComments(task_id);
                        $('#replyBox' + id).hide();
                    })
                    .catch(error => {

                    })
            },
            showLog() {
                var _this = this;
                _this.task_logs = [];
                axios.get('/api/task-list/get-log/' + _this.selectedData.cardId)
                    .then(response => response.data)
                    .then(response => {
                        _this.task_logs = response;
                    })
                    .catch(error => {
                        console.log('Api for move down task not Working !!!')
                    });
            },
            sendMail(data) {
                axios.post('/api/send-mail/', data)
                    .then(response => response.data)
                    .then(response => {
                        console.log(response);
                    })
                    .catch(error => {

                    });
            },
            onPasteFile() {
                let _this = this;
                $('#comment' + _this.selectedData.cardId).on('paste', function (event) {
                    var items = (event.clipboardData || event.originalEvent.clipboardData).items;
                    var blob = null;
                    for (var i = 0; i < items.length; i++) {
                        if (items[i].type.indexOf("image") === 0) {
                            blob = items[i].getAsFile();
                        }
                    }

                    if (blob !== null) {
                        var reader = new FileReader();
                        reader.onload = function (event) {
                            var ImageURL = event.target.result;
                            var block = ImageURL.split(";");
                            var contentType = block[0].split(":")[1];
                            var realData = block[1].split(",")[1];
                            var blob = _this.b64toBlob(realData, contentType);
                            var formData = new FormData();
                            formData.append('file', blob);
                            formData.append('id', _this.selectedData.cardId);
                            _this.uploadCommentFile(formData);
                        };
                        reader.readAsDataURL(blob);
                    }
                });
            },
            b64toBlob(b64Data, contentType, sliceSize) {
                contentType = contentType || '';
                sliceSize = sliceSize || 512;
                var byteCharacters = atob(b64Data);
                var byteArrays = [];
                for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
                    var slice = byteCharacters.slice(offset, offset + sliceSize);
                    var byteNumbers = new Array(slice.length);
                    for (var i = 0; i < slice.length; i++) {
                        byteNumbers[i] = slice.charCodeAt(i);
                    }
                    var byteArray = new Uint8Array(byteNumbers);
                    byteArrays.push(byteArray);
                }
                var blob = new Blob(byteArrays, {type: contentType});
                return blob;
            },
            uploadCommentFile(data) {
                let _this = this;
                axios.post('/api/comment-file-upload', data, {headers: {'Content-Type': 'multipart/form-data'}})
                    .then(response => response.data)
                    .then(response => {
                        _this.comment.push(response.Data);
                        setTimeout(() => {
                            $('[data-toggle="tooltip"]').tooltip();
                        }, 100);
                    })
                    .catch(error => {
                        console.log('Api for task date update not Working !!!')
                    });
            },
            showSingleView(task) {
                var _this = this;
                var nav_type = JSON.parse(localStorage.selected_nav);
                let routeData = this.$router.resolve({
                    name: 'single-task-view',
                    params: {projectId: nav_type.project_id, type: 'lists', task_id: btoa(task.id), id: task.list_id}
                });
                window.open(routeData.href, '_blank');
            },
            makeInput(e, data) {
                var _this = this;

                _this.empty_task_delete_flag = data.id;
                if (data.text === 'Dont Forget Section') {
                    $(e.target).attr('disabled', 'disabled');
                } else {
                    $('.inp').addClass('input-hide').removeClass('form-control');
                    $(e.target).removeClass('input-hide').addClass('form-control');
                }
            },
            saveData(e, data) {
                let id = data.id;
                this.updateTitle(id, data)
            },
            updateTitle(id, data) {

                var _this = this;
                var text = _this.selectedData.data;
                var postData = {
                    id: _this.selectedData.cardId,
                    title: text
                };
                axios.post('/api/card-update/' + _this.selectedData.cardId, postData)
                    .then(response => response.data)
                    .then(response => {
                        Bus.$emit('detailsUpdate', {
                            data: response.data
                        })
                        _this.HideDetails();
                        Bus.$emit('taskUpdate', {
                            list_id: _this.selectedData.list_id
                        })
                        _this.Socket.emit('taskUpdate', {
                            project_id: _this.selectedData.project_id,
                            list_id: _this.selectedData.list_id,
                            board_id: _this.selectedData.multiple_board_id,
                            user_id: _this.authUser.id,
                            task_id : _this.selectedData.id
                        })
                    })
                    .catch(error => {
                        console.log('Api for move down task not Working !!!');
                    });
            },
            commentEdit(cardId){
                let _this = this;
                CKEDITOR.plugins.get('bbcode');
                let token = Spark.csrfToken; //_this.getMeta('csrf-token');
                _this.cmntEditors = CKEDITOR.replace('comment'+cardId, {
                    filebrowserUploadUrl: '/api/card-file-upload?type=json',
                    filebrowserUploadMethod: 'xhr',
                    fileTools_requestHeaders: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token
                    }
                });

                this.cmntEditors.on("instanceReady", function(){
                    this.document.on("keyup", function(evt){
                        evt = evt.data.$;
                        let datas = _this.cmntEditors.getData();
                        _this.commentPress(evt, cardId, datas)

                    });
                });

            },
            commentEdit2(elId){
                let _this = this;
                CKEDITOR.plugins.get('bbcode');
                let token = Spark.csrfToken; //_this.getMeta('csrf-token');
                _this.editAndReplay = CKEDITOR.replace('replyTextBox'+elId, {
                    filebrowserUploadUrl: '/api/card-file-upload?type=json',
                    filebrowserUploadMethod: 'xhr',
                    fileTools_requestHeaders: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token
                    }
                });

                this.editAndReplay.on("instanceReady", function(){
                    this.document.on("keyup", function(reEvt){
                        reEvt = reEvt.data.$;
                        let datas = _this.editAndReplay.getData();
                        _this.commentReplyPress(reEvt, elId, datas)
                    });
                });
            },

            searchTags(index, key) {
                let _this = this;
                let data = {
                    title: _this.searchTag
                };

                axios.post('/api/search-tag', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.selectedData.allTags = response.allTags;
                    })
                    .catch(error => {
                        console.log('Api not working');
                    });
            },

            suggestUsers() {
                let _this = this;
                let data = {
                    project_id: _this.$route.params.projectId,
                    name: _this.userSeggistion
                }

                axios.post('/api/project-users', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.allTeamsUsers = response.users;
                    })
                    .catch(error => {

                    });
            },
        },
        directives: {
            ClickOutside
        },
        watch: {
        }
    }
</script>

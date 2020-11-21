<template>
    <div>
        <div @click="HideDetails" id="board_view_list">
            <div class="loder" id="loder-hide">
                <div class="foo foo1">
                    <div class="circle"></div>
                </div>
                <div class="foo foo2">
                    <div class="circle"></div>
                </div>
            </div>
            <div style="padding-left: 2%">
                <div class="col-12 action-task" id="board_title">
                    <h2 class="p-t-10" >
                        {{ list.name }}
                    </h2>
                    <p class="compltit-p" style="overflow-y: auto;height: 25px;" v-if="list.description != null">
                        {{ list.description }}</p>
                </div>
            </div>
            <div class="col-12" id="col10" style="border: none">
                <div class="card-scene">
                    <Container
                        :drop-placeholder="upperDropPlaceholderOptions"
                        @drag-start="dragStart"
                        @drop="onColumnDrop($event)"
                        drag-handle-selector=".column-drag-handle"
                        orientation="horizontal">
                        <Draggable :key="column.id" v-for="(column , index ) in scene.children">
                            <div class="hidden-column" v-if="column.hidden">
                                <div class="card-column-header">
                                    <span @click="showColumn(index, column.boardId)"
                                          class="column-drag-handle">&#8667;</span>

                                </div>
                            </div>
                            <div :class="[column.props.className, column.props.className+'-'+column.boardId ]"
                                 @click="selectColumn(column.boardId)" v-else>
                                <div class="card-column-header">
                                    <div class="row" style="position: relative;">
                                        <div class="col-md-3" style="margin: 0px;  padding : 0px;">
                                            <span class="column-drag-handle">&nbsp; &#x2630;</span>
                                            <img :src="baseUrl+'/img/'+column.progress+'.png'" height="40" width="40"/>
                                        </div>
                                        <div class="col-md-6 " style="margin: 0px; padding : 0px;word-break: break-all">
                                            <span :title="column.name"
                                                  class="col_name"
                                                  data-placement="bottom" data-toggle="tooltip"
                                                  style="word-break: break-all; max-height: 71px; display: block; height: 42px; overflow: hidden;">
                                                {{ (column.name && column.name.length > 38)? column.name.substring(0,38)+ " .." : column.name }}
                                            </span>
                                        </div>
                                        <div style="margin: 0px; padding : 0px;">
                                            <span class="total-task">{{column.total_card}}</span>
                                        </div>
                                        <div class="col-md-1"
                                             style="position: absolute; display: inline; right: 10px; top: 9px">
                                            <span class="pull-right" style="display: inline-flex;">
                                                <span class="dropdown-toggle-split opacity"
                                                      data-toggle="dropdown" style="padding: 0px;">
                                                   <i class="fal fa-ellipsis-h" style="font-size:22px"></i>
                                                </span>
                                                <div class="dropdown-menu card-option-dropdown">
                                                    <diV class="collapse show switchToggle">
                                                        <a @click="addExistingTask(index,column.boardId)"
                                                           class="dropdown-item"
                                                           href="javascript:void(0)"
                                                           v-if="column.moveToCol === false || column.ruleType === 'asnUser'">
                                                            <i class="fal fa-layer-plus mr-2"></i>
                                                            Add existing tasks
                                                        </a>
                                                        <a @click="addCard(index,column.boardId)"
                                                           class="dropdown-item"
                                                           href="javascript:void(0)"
                                                           v-if="column.moveToCol === false || column.ruleType === 'asnUser'">
                                                            <i class="fal fa-plus-square mr-2"></i>
                                                            Create new tasks
                                                        </a>
                                                        <div class="dropdown-divider"
                                                             v-if="column.moveToCol === false || column.ruleType === 'asnUser'"></div>
                                                        <a @click="updateColumSow(index)" class="dropdown-item"
                                                           href="#">
                                                            <i class="fal fa-edit mr-2"></i>
                                                            Edit column
                                                        </a>
                                                        <a @click="hideColumn(index, column.boardId)"
                                                           class="dropdown-item" href="#">
                                                            <i class="far fa-eye-slash mr-2"></i>
                                                            Hide column
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a @click="transferColumnToOtherBoard(index, column.boardId)"
                                                           class="dropdown-item" href="#">
                                                            <i class="fad fa-exchange-alt mr-2"></i>
                                                            Transfer Column to another board
                                                        </a>
                                                        <a @click="showLinkModel(index, column.boardId)"
                                                           class="dropdown-item" href="#"
                                                           v-if="column.moveToCol === false || column.ruleType === 'asnUser'">
                                                            <i class="fas fa-link mr-2"></i>
                                                            Link to List
                                                        </a>
                                                        <li class="dropdown-submenu"
                                                            v-if="column.linkToList.length > 0">
                                                            <a class="dropdown-item" href="#">
                                                                <i class="fas fa-unlink mr-2"></i>
                                                                Unlink Lists
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li @click="unlinklistToCol(index, column.boardId, unlinks.id)"
                                                                    class="dropdown-item"
                                                                    v-for="unlinks in column.linkToList">
                                                                    <a href="#"><img
                                                                        :src="baseUrl+'/img/task-icon/unlink.png'"
                                                                        height="18" width="18">
                                                                         {{(unlinks.link_to_list_column !== null && unlinks.link_to_list_column !== undefined) ? unlinks.link_to_list_column.list_title : ''}}
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                            <div class="dropdown-divider"></div>
                                                            <a @click="deleteColumn(index,column.boardId)"
                                                               class="dropdown-item"
                                                               href="#">
                                                                <i aria-hidden="true" class="fal fa-trash-alt mr-2"
                                                                   data-v-0ca4b43b=""></i>
                                                                Delete column</a>
                                                        </diV>
                                                    </div>
                                            </span>
                                        </div>
                                        <div :id="column.boardId+'rulesCard'"
                                             style="float: left; position: absolute; top: 55px; padding: 0px 9px;"
                                             v-if="column.moveToCol === true || column.ruleType === 'asnUser'">
                                            <div class="card-list card">
                                                <span class="text-warning"
                                                      v-html="'<strong>Rule is paused</strong>'"
                                                      v-if="column.ruleStatus === 0"></span>
                                                <span
                                                    v-html="'This column has rule <strong>'+column.ruleName+'</strong> and moves cards to <strong>'+column.boardName+'</strong> Board in Column <strong>'+column.moveToColName+'</strong>'"
                                                    v-if="column.ruleType === 'mvCard'"></span>
                                                <span
                                                    v-html="'This column has rule <strong>'+column.ruleName+',</strong> assign to <strong>'+ column.usersName +'</strong>'"
                                                    v-if="column.ruleType === 'asnUser'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <Container
                                    :drop-placeholder="dropPlaceholderOptions"
                                    :get-child-payload="getCardPayload(column.id)"
                                    :id="'coll'+column.boardId"
                                    :style="{'padding-top': ruleAlertHeight(column.boardId+'rulesCard', 'coll'+column.boardId)}"

                                    @drop="(e) => onCardDrop(column.id, column.boardId, index, e)"
                                    drag-class="card-ghost"
                                    drop-class="card-ghost-drop"
                                    group-name="col"
                                >
                                    <Draggable :key="card.id" v-for="(card , key) in column.children">
                                        <div
                                            :class="[card.props.className,(card.priority_label !== null) ? 'pc-'+card.priority_label : '']"
                                            :id="'card_'+card.cardId"
                                            :style="card.props.style"
                                            @click="makeItClick($event, card, column.children, index, key, column.boardId)"
                                            @contextmenu="makeItClick($event, card,column.children, index, key, column.boardId)"
                                            class="card-list"
                                            v-if="card.data !== 'Dont Forget Section'"
                                            v-on:dblclick="showLog(card)">
                                            <div :id="'titleUserMention'+card.cardId" class="dropdowns-card-user"
                                                 style="z-index: 1;">
                                                <diV class="collapse show switchToggle">
                                                    <ul class="myUL-user-card" id="myUL-user"
                                                        style="background: #f3f3f3; border-radius: 5px; border: 1px solid #d4d4d4; ">
                                                        <template v-for="user in projectUsers"
                                                                  v-if="projectUsers !== null && projectUsers.length > 0">
                                                            <li @click="SearchTaskByAssignedUsers(user.id, user.name, card, user)">
                                                                <a href="javascript:void(0)">
                                                                        <span class="assignUser-suggest-photo">
                                                                            <span v-if="user.photo_url === null"> {{(user.name !== null) ? user.name.substring(0,2) : ''}} </span>
                                                                            <span v-if="user.photo_url !== null"> <img
                                                                                :alt="user.name"
                                                                                :src="user.photo_url"
                                                                                class="assignUser-photo-for-card"
                                                                                style="margin-top: -3px;"/> </span>
                                                                        </span>
                                                                    {{user.name}}
                                                                </a>
                                                            </li>
                                                        </template>
                                                        <template v-for="(user, tagIndx) in card.existing_tags"
                                                                  v-if="card.existing_tags !== null && card.existing_tags.length > 0 && projectUsers === null ">
                                                            <li @click="tagMention(card, user, index , tagIndx, key)"
                                                                class="users-select row">
                                                                <div class="col-md-9 add-tag-to-selected">
                                                                    <span
                                                                        :style="{'background' : user.color}"
                                                                        class="badge badge-default tag-color-custom-contextmenu">.</span>
                                                                    <h5>{{user.title}}</h5>
                                                                </div>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                </diV>
                                            </div>
                                            <span
                                                :class="[(card.priority_label !== null) ? 'pch-'+card.priority_label : 'ch-option']">
                                                <span class="dropdown-toggle-split opacity"
                                                      data-toggle="dropdown">
                                                    <i :class="[(card.priority_label !== null) ? 'ch-option-icon' : '']"
                                                       class="fal fa-ellipsis-h"
                                                       style="color: #272757; font-size:22px;"></i>
                                                </span>
                                                <div class="dropdown-menu card-option-dropdown">
                                                    <diV class="collapse show switchToggle">

                                                        <a @click="showTransferModel(index, key, card.cardId, column.boardId)"
                                                           class="dropdown-item" href="#"
                                                           title="Transfer To Other Board Or Column">
                                                            <i class="fad fa-sort-alt mr-2"></i> Transfer task to another board
                                                        </a>

                                                        <div class="dropdown-divider"
                                                             v-if="card.types === 'task'"></div>
                                                        <a @click="deleteTask(index, key, card.cardId)"
                                                           class="dropdown-item" href="#"
                                                           title="Remove Task" v-if="card.types === 'task'">
                                                            <i class="fal fa-minus-octagon mr-2"></i>
                                                            Remove task from <strong> This Board </strong>
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a @click="RemoveNodeAndChildren()" class="dropdown-item"
                                                           href="#">
                                                            <i class="fad fa-trash-alt mr-2"></i>
                                                            Delete the task
                                                        </a>
                                                    </diV>
                                                </div>
                                            </span>

                                            <div :id="'title'+card.cardId" @blur="showItem($event,card,index,key)"
                                                 @click="makeInput($event,card.cardId)"
                                                 @keypress="preventEnter($event)"
                                                 @keyup="cardTitlePress($event,card,index,key)"
                                                 class="card-title-blur card-title-show"
                                                 contenteditable="true"
                                                 v-html="card.data">
                                            </div>
                                            <div>
                                                <a class="calender clickHide" v-if="card.date === null">
                                                    <i class="fal fa-calendar-plus icon-image-preview" data-toggle
                                                       title="toggle"></i>
                                                </a>
                                                <datepicker
                                                    :disabled-dates="disabledDates"
                                                    @selected="updateDate"
                                                    calendar-button-icon='<i class="outline-event icon-image-preview"></i>'
                                                    data-placement="right"
                                                    data-toggle="tooltip"
                                                    format='dd MMM' input-class="dateCal" title="Due Date"
                                                    v-model="card.date">
                                                </datepicker>

                                            </div>
                                            <div class="card-delete-icon">
                                                <a @click="deleteCard(index, key, card.cardId)"
                                                   v-if="card.types === 'card'">
                                                    <i class="fal fa-trash-alt icon-image-preview"></i>
                                                </a>
                                                <a @click="deleteTask(index, key, card.cardId)"
                                                   v-if="card.types === 'task'">
                                                    <i class="fal fa-trash-alt icon-image-preview"></i>
                                                </a>
                                            </div>
                                            <a class="user-assign-card">
                                                <template v-if="card.assigned_user.length > 0">
                                                    <span class="assigned_user-card "
                                                          data-toggle="dropdown"
                                                          v-for="(assign,keyId) in card.assigned_user">
                                                        <p :title="assign.name"
                                                           class="assignUser-photo-for-card text-uppercase"
                                                           data-placement="bottom"
                                                           data-toggle="tooltip" style="margin-right: 1px;"
                                                           v-if="keyId <= 1">
                                                           <span v-if="assign.photo_url === null"> {{(assign.name !== null) ? assign.name.substring(0,2) : ''}} </span>
                                                           <span v-if="assign.photo_url !== null"> <img
                                                               :alt="assign.name" :src="assign.photo_url"
                                                               class="assignUser-photo-for-card"
                                                               style="margin-top: -3px;"/> </span>
                                                        </p>
                                                    </span>
                                                </template>
                                                <span class=" dropdown-toggle-split" data-toggle="dropdown" v-else>
                                                    <i class="fal fa-user-plus icon-image-preview "
                                                       data-toggle="tooltip"
                                                       title="Assignee"></i>
                                                </span>

                                                <div class="dropdown-menu dropdown-menu-right" style="z-index: 1;">
                                                    <diV class="collapse show switchToggle">
                                                        <li class="assignUser">
                                                            <input @keyup="suggestUsers(index, key)"
                                                                   class="input-group searchUser assignUserInput"
                                                                   placeholder="Assign user search by: First or last or email"
                                                                   type="text"
                                                                   v-model="userSeggistion">
                                                            <label class="pl-2 label-text">
                                                                <span class="assign-user-drop-down-text">
                                                                    Or invite a new member by email address
                                                                </span>
                                                            </label>
                                                        </li>
                                                        <li class="assignUser auHeight"  style="padding-right : 5px ">
                                                            <!-- card.users -->
                                                            <template>
                                                                <div v-for="user in allTeamsUsers" disabled
                                                                     v-if="card.assigned_user_ids.includes(user.id) === true"
                                                                     class="row active-user-board disabled">
                                                                    <div class="col-md-2 pt-1 pl-2">
                                                                        <p class="assignUser-photo" v-if="user.photo_url === null">
                                                                            {{(user.name !== null) ? user.name.substring(0,2) : ''}}
                                                                        </p>
                                                                        <p class="assignUser-photo" v-if="user.photo_url !== null">
                                                                            <img :alt="user.name" :src="user.photo_url" class="assignUser-photo-for-card" style="margin-top: -3px; width: 28px; height: 28px;"/>
                                                                        </p>

                                                                    </div>
                                                                    <div class="col-md-9 assign-user-name-email"
                                                                         style="word-break: break-all;">
                                                                        <h5 style=" width: 85%;">{{user.name}}<br>
                                                                            <small>{{user.email}}</small>
                                                                        </h5>
                                                                    </div>
                                                                    <a :id="'remove-assign-user'+user.id"
                                                                       @click="removeAssignedUser(user, card)"
                                                                       data-toggle="tooltip"
                                                                       title="Remove user from assigned !"
                                                                       class="remove-assign-user badge badge-danger"
                                                                       href="javascript:void(0)">
                                                                        <i class="fal fa-user-times"></i>
                                                                    </a>
                                                                </div>
                                                                <div v-for="user in allTeamsUsers"
                                                                     v-if="card.assigned_user_ids.includes(user.id) === false"
                                                                     class="row users-select-board"
                                                                     @click="assignUserToTask(user, index, key, card) ">
                                                                    <div class="col-md-2 pt-1 pl-2">
                                                                        <p class="assignUser-photo"
                                                                           v-if="user.photo_url === null">
                                                                            {{(user.name !== null) ?
                                                                            user.name.substring(0,2) : ''}}
                                                                        </p>
                                                                        <p class="assignUser-photo"
                                                                           v-if="user.photo_url !== null">
                                                                            <img class="assignUser-photo-for-card"
                                                                                 :src="user.photo_url" :alt="user.name"
                                                                                 style="margin-top: -3px; width: 28px; height: 28px;"/>
                                                                        </p>

                                                                    </div>
                                                                    <div class="col-md-9 assign-user-name-email"
                                                                         style="word-break: break-all;">
                                                                        <h5 style=" width: 85%;">{{user.name}}<br>
                                                                            <small>{{user.email}}</small>
                                                                        </h5>
                                                                    </div>
                                                                    <a :id="'remove-assign-user'+user.id"
                                                                       data-toggle="tooltip"
                                                                       title="Assign user to task!"
                                                                       class="remove-assign-user badge badge-success"
                                                                       href="javascript:void(0)">
                                                                        <i class="fal fa-user"></i>
                                                                    </a>
                                                                </div>
                                                            </template>
                                                        </li>
                                                    </diV>
                                                </div>
                                            </a>
                                            <a class="tag-icon">
                                                <div v-if="card.tags && card.tags.length !== 0">
                                                    <div style="float: left;" v-for="(item, tagIndex) in card.tags">
                                                        <div class="dropdown-toggle-split "
                                                             data-toggle="dropdown"
                                                             style="padding-right: 0px; padding-left: 1px;"
                                                             v-if="tagIndex < 2">
                                                            <span class="badge badge-danger "
                                                                  v-if='item === "Dont Forget"'>
                                                                {{item.text.substring(0,12)}}
                                                            </span>
                                                            <span :title="card.tagTooltip"
                                                                  class="badge badge-success"
                                                                  data-placement="bottom"
                                                                  data-toggle="tooltip"
                                                                  style="height: 18px;line-height: 14px;"
                                                                  v-bind:style="[{'background': item.color },{'margin-left' : 1 +'px'}]"
                                                                  v-else>
                                                                {{item.text.substring(0,5)}}
                                                                <span v-if="item.text.length > 5">.</span>
                                                            </span>
                                                        </div>

                                                        <div :id="'dropdown1'+card.cardId"
                                                             class="dropdown-menu dropdown-menu1">

                                                            <diV class="collapse show switchToggle" style="">
                                                                <div class="container-fluid">
                                                                    <input @keyup="searchTags(index, key, 'single')"
                                                                           class="input-group tagInput"
                                                                           placeholder="Search Tags"
                                                                           type="text"
                                                                           v-model="searchTag"
                                                                           v-if="filterTag === 1">
                                                                    <vue-tags-input
                                                                        :allow-edit-tags="true"
                                                                        :tags="card.tags"
                                                                        @before-deleting-tag="deleteTag => deleteCardTag(deleteTag,card,index,key)"
                                                                        @tags-changed="newTags => (changeTag(newTags,card,index,key))"
                                                                        v-model="tag"/>
                                                                    <div class="row">
                                                                        <div class="col-12 tag-section">
                                                                            <div v-if="searchTag">
                                                                                <template
                                                                                    v-for="(stag, stagIndx) in searchAllTags">
                                                                                    <li @click="addExistingTagSearch(index , stagIndx, key, card, searchAllTags, '')"
                                                                                        class="badge-pill tags"
                                                                                        v-bind:style="[{'background': stag.color },{'margin-left' : 1 +'px'}]"
                                                                                        v-if="stag.text !== 'Dont Forget'">
                                                                                        {{(stag.title !== undefined)
                                                                                        ?stag.title.substring(0,12) :
                                                                                        ''}}
                                                                                    </li>
                                                                                </template>
                                                                            </div>

                                                                            <div v-if="searchTag === null || searchTag === ''">
                                                                                <template
                                                                                    v-for="(tag, tagIndx) in card.existing_tags">
                                                                                    <li @click="addExistingTag(index , tagIndx, key, card, '')"
                                                                                        class="badge-pill tags"
                                                                                        v-bind:style="[{'background': tag.color },{'margin-left' : 1 +'px'}]"
                                                                                        v-if="tag.text !== 'Dont Forget'">
                                                                                        {{(tag.title !== undefined) ?
                                                                                        tag.title.substring(0,12) : ''}}
                                                                                    </li>
                                                                                </template>
                                                                                <li @click="addExistingTag(index , 0, key, card, 'Dont Forget')"
                                                                                    class="badge-pill tags"
                                                                                    style="background: #FB8678"> Dont
                                                                                    Forget
                                                                                </li>
                                                                            </div>
                                                                            <li @click="addExistingTag(index , 0, key, card, 'Dont Forget')"
                                                                                class="badge-pill tags"
                                                                                style="background: #FB8678;height: 17px;line-height: 14px;">
                                                                                Dont Forget
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
                                                    </div>
                                                </div>
                                                <i class="fal fa-tags icon-image-preview" data-toggle="dropdown" title="Add Tag" v-else></i>
                                                <div class="dropdown-menu dropdown-menu1 ">

                                                    <diV class="collapse show switchToggle" style="">
                                                        <div class="container-fluid">
                                                            <input @keyup="searchTags(index, key, 'single')"
                                                                   class="input-group tagInput"
                                                                   placeholder="Search Tags"
                                                                   type="text"
                                                                   v-model="searchTag"
                                                                   v-if="filterTag === 1">

                                                            <vue-tags-input
                                                                :allow-edit-tags="true"
                                                                :tags="tag1"
                                                                @before-deleting-tag="deleteTag => deleteCardTag(deleteTag,card,index,key)"
                                                                @tags-changed="newTags => (changeTag(newTags,card,index,key))"
                                                                v-model="tag"/>

                                                            <div class="row">
                                                                <div class="col-12 tag-section">
                                                                    <div v-if="searchTag">
                                                                        <template
                                                                            v-for="(stag, stagIndx) in searchAllTags">
                                                                            <li @click="addExistingTagSearch(index , stagIndx, key, card,searchAllTags, '')"
                                                                                class="badge-pill tags"
                                                                                v-bind:style="[{'background': stag.color },{'margin-left' : 1 +'px'}]"
                                                                                v-if="stag.text !== 'Dont Forget'">
                                                                                {{(stag.title !== undefined)
                                                                                ?stag.title.substring(0,12) : ''}}
                                                                            </li>
                                                                        </template>
                                                                    </div>

                                                                    <div v-if="searchTag === null || searchTag === ''">
                                                                        <template
                                                                            v-for="(tag, tagIndx) in card.existing_tags">
                                                                            <li @click="addExistingTag(index , tagIndx, key, card, '')"
                                                                                class="badge-pill tags"
                                                                                v-bind:style="[{'background': tag.color },{'margin-left' : 1 +'px'}]"
                                                                                v-if="tag.text !== 'Dont Forget'">
                                                                                {{(tag.title !== undefined) ?
                                                                                tag.title.substring(0,12) : ''}}
                                                                            </li>
                                                                        </template>
                                                                        <li @click="addExistingTag(index , 0, key, card, 'Dont Forget')"
                                                                            class="badge-pill tags"
                                                                            style="background: #FB8678"> Dont Forget
                                                                        </li>
                                                                    </div>
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
                                            <a class="priority-icon-card" data-toggle="tooltip" title="Priority">
                                                <span class="priority-icon dropdown-toggle-split"
                                                      data-toggle="dropdown"
                                                      style="top: 12px;"
                                                      v-if="card.priority_label !== null">
                                                     <span class="badge badge-warning text-capitalize "
                                                           data-original-title="" data-placement="bottom"
                                                           data-toggle="tooltip"
                                                           style="background: #e25858;height: 18px; line-height: 13px;color:#ffffff"
                                                           title=""
                                                           v-if="card.priority_label === 'high'">
                                                        {{card.priority_label}}
                                                    </span>
                                                    <span class="badge badge-warning text-capitalize "
                                                          data-original-title="" data-placement="bottom"
                                                          data-toggle="tooltip"
                                                          style="background: #5987d1;height: 18px; line-height: 13px;color:#ffffff"
                                                          title=""
                                                          v-if="card.priority_label === 'low'">
                                                        {{card.priority_label}}
                                                    </span>
                                                    <span class="badge badge-warning text-capitalize "
                                                          data-original-title="" data-placement="bottom"
                                                          data-toggle="tooltip"
                                                          style="background: #e58c62;height: 18px; line-height: 13px;color:#ffffff"
                                                          title=""
                                                          v-if="card.priority_label === 'medium'">
                                                        {{card.priority_label}}
                                                     </span>
                                                </span>
                                                <i class="fal fa-exclamation-triangle icon-image-preview"
                                                   data-toggle="dropdown"
                                                   style="margin-bottom: -2px"
                                                   title="Add Priority" v-else></i>

                                                <div class="dropdown-menu dropdown-menu-right"
                                                     style="z-index: 1;width: 185px; height: 156px">
                                                    <div class="collapse show switchToggle">
                                                        <ul>
                                                            <li class="assignUser">
                                                                <label class="pl-2 label-text">
                                                                <span class="assign-user-drop-down-text">
                                                                    Select Task Priority
                                                                </span>
                                                                </label>
                                                            </li>
                                                            <li class="assignUser">

                                                                <div class="users-select row"
                                                                     style="margin: -1px 0px 0px 0px;">
                                                                    <div @click="Add_Priority('3',card)"
                                                                         class="col-md-9 add-tag-to-selected">
                                                                    <span
                                                                        class="badge badge-default tag-color-custom-contextmenu"
                                                                        style="background: #e25858;">.</span>
                                                                        <h5 class="text-capitalize"> high</h5>
                                                                    </div>
                                                                    <div @click="RemovePriority(card)"
                                                                         style=" width: 20%; text-align: right;padding-top: 4px;font-size: 16px"
                                                                         v-if="card.priority_label === 'high'">
                                                                    <span>
                                                                        <i class="far fa-minus-octagon"></i>
                                                                    </span>
                                                                    </div>
                                                                </div>
                                                                <div class="users-select row"
                                                                     style="margin: -1px 0px 0px 0px;">
                                                                    <div @click="Add_Priority('2',card)"
                                                                         class="col-md-9 add-tag-to-selected">
                                                                    <span
                                                                        class="badge badge-default tag-color-custom-contextmenu"
                                                                        style="background: #e58c62;">.</span>
                                                                        <h5 class="text-capitalize">medium</h5>
                                                                    </div>
                                                                    <div @click="RemovePriority(card)"
                                                                         style=" width: 20%; text-align: right;padding-top: 4px;font-size: 16px"
                                                                         v-if="card.priority_label === 'medium'">
                                                                        <i class="far fa-minus-octagon"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="users-select row"
                                                                     style="margin: -1px 0px 0px 0px;">
                                                                    <div @click="Add_Priority('1',card)"
                                                                         class="col-md-9 add-tag-to-selected">
                                                                    <span
                                                                        class="badge badge-default tag-color-custom-contextmenu"
                                                                        style="background: #5987d1;">.</span>
                                                                        <h5>Low</h5>
                                                                    </div>
                                                                    <div @click="RemovePriority(card)"
                                                                         style=" width: 20%; text-align: right;padding-top: 4px;font-size: 16px"
                                                                         v-if="card.priority_label === 'low'">
                                                                        <i class="far fa-minus-octagon"></i>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </a>
                                            <div :class="[(card.priority_label !== null) ? 'pch-total-child' : 'total-child']">
                                                <span @click="hideChilds(card.cardId)" v-if="card.child > 0 ">
                                                    <i class="fal fa-layer-minus" style="font-size: 14px;"
                                                       v-if="card.open === 0"></i>
                                                    <i class="fal fa-layer-plus" style="font-size: 14px;"
                                                       v-if="card.open === 1"></i>
                                                    <i class="fal fa-layer-minus" style="font-size: 14px;"
                                                       v-if="card.open === null"></i>
                                                    <strong>
                                                        {{ card.child }}
                                                    </strong>
                                                </span>
                                                <span title="Comments" v-if="card.comment.length > 0">
                                                    <i class="fal fa-comments"></i>
                                                </span>
                                                <span @click="showDescription(card)" title="Description"
                                                      v-if="card.description !== ''">
                                                    <i class="fal fa-align-justify"></i>
                                                </span>
                                                <span :title="card.list_data.list_title" style="margin-left: 5px;"
                                                      v-if="card.list_data !== null">
                                                    {{ card.list_data.list_title.substring(0,15)}}<span
                                                    v-if="card.list_data.list_title.length > 15">...</span>
                                                </span>
                                            </div>
                                        </div>
                                    </Draggable>
                                </Container>
                            </div>
                        </Draggable>
                        <div>
                            <p @click="addColumn" class="add-column" v-if="board_id">
                                <i class="fa fa-plus"></i>
                                add column
                            </p>
                        </div>
                    </Container>
                    <div class="jquery-accordion-menu" id="jquery-accordion-menu" v-click-outside="HideCustomMenu">
                        <ul>
                            <li>
                                <a class="dropdown-toggle-split "
                                   data-toggle="dropdown" href="javascript:void(0)">
                                    <i aria-hidden="true" class="fal fa-user-plus "></i>
                                    &nbsp; Assign User to Selected
                                </a>
                                <span class="contex-menu-sortcut">
                                    <span class="badge-pill badge-default">Ctrl</span>+<span
                                    class="badge-pill badge-default">U</span>
                                </span>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <div class="collapse show switchToggle">
                                        <ul>
                                            <li class="assignUser">
                                                <input @keyup="suggestUsers(index, key)"
                                                       class="input-group searchUser assignUserInput"
                                                       placeholder="Assign user search by: First or last or email"
                                                       type="text"
                                                       v-model="userSeggistion">
                                                <label class="pl-2 label-text">
                                                    <span class="assign-user-drop-down-text">
                                                        Or invite a new member by email address
                                                    </span>
                                                </label>
                                            </li>
                                            <li class="assignUser auHeight">
                                                <template v-for="user in allTeamsUsers">
                                                    <div @click="ActionToSelectedTask(user.id,'user')"
                                                         class="users-select-board row">
                                                        <div class="col-md-2 pt-1 pl-2">
                                                            <p class="assignUser-photo" v-if="user.photo_url === null">
                                                                {{(user.name !== null) ? user.name.substring(0,2) : ''}}
                                                            </p>
                                                            <p class="assignUser-photo" v-if="user.photo_url !== null">
                                                                <img :alt="user.name"
                                                                     :src="user.photo_url"
                                                                     class="assignUser-photo-for-card"
                                                                     style="margin-top: -3px; width: 28px; height: 28px;"/>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-10 assign-user-name-email">
                                                            <h5 style="width: 95%; word-break: break-all;">{{user.name}}<br>
                                                                <small>{{user.email}}</small>
                                                            </h5>
                                                        </div>
                                                    </div>
                                                </template>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li>

                                <a @click="deleteSelectedTask" href="javascript:void(0)">
                                    <i class="fal fa-trash-alt"></i>
                                    &nbsp; Delete Selected
                                    <span class="badge-pill badge-default contex-menu-sortcut">Delete</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-toggle-split " data-toggle="dropdown" href="javascript:void(0)">
                                    <i aria-hidden="true" class="fal fa-tags contex-menu-icon"></i>
                                    Add Tags
                                </a>
                                <span class="contex-menu-sortcut">
                                    <span class="badge-pill badge-default">Shift</span>+
                                    <span class="badge-pill badge-default">#</span>
                                </span>
                                <div class="dropdown-menu dropdown-menu-right" style="width: 240px;">
                                    <div class="collapse show switchToggle">
                                        <ul>
                                            <li class="assignUser">
                                                <label class="pl-2 label-text pb-2">
                                                    <span class="assign-user-drop-down-text">
                                                        Select Tag
                                                    </span>
                                                </label>

                                                <input @keyup="searchTags(index, key, 'multi')"
                                                       class="input-group tagInput mb-0"
                                                       placeholder="Search Tags"
                                                       type="text"
                                                       v-model="searchTag">
                                            </li>
                                            <li class="assignUser contexTag">
                                                <div v-if="allTags.length > 0">
                                                <template v-for="tag in allTags">
                                                    <div @click="ActionToSelectedTask(tag.id,'tag')"
                                                         class="users-select row" style="margin: 0 -1px -1px -1px">
                                                        <div class="col-md-9 add-tag-to-selected">
                                                            <span
                                                                :style="{'background' : tag.color}"
                                                                class="badge badge-default tag-color-custom-contextmenu">.</span>
                                                            <h5>{{tag.title}}</h5>
                                                        </div>
                                                    </div>
                                                </template>
                                                </div>
                                                <div v-else>
                                                    <template>
                                                        <div class="users-select row" style="margin: 0 -1px -1px -1px">
                                                            <div disabled class="col-md-9 text-center add-tag-to-selected">
                                                                <h5 class="text-danger">Tag not found</h5>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <a @click="showTransferModel(currentColumn, '', '', currentColumnIndex)">
                                    <i class="far fa-compress"></i>
                                    &nbsp; Move to another column or Board
                                </a>
                                <span class="contex-menu-sortcut">
                                    <span class="badge-pill badge-default">Ctrl</span>+
                                    <span class="badge-pill badge-default">M</span>
                                </span>
                            </li>
                            <li>
                                <a class="dropdown-toggle-split " data-toggle="dropdown" href="javascript:void(0)">
                                    <i class="fal fa-exclamation-triangle contex-menu-icon"></i>
                                    Add Priority
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <div class="collapse show switchToggle">
                                        <ul>
                                            <li class="assignUser">
                                                <label class="pl-2 label-text">
                                                        <span class="assign-user-drop-down-text">
                                                            Select Task Priority
                                                        </span>
                                                </label>
                                            </li>
                                            <li class="assignUser">
                                                <div @click="Add_Priority('3',null)"
                                                     class="users-select row m-0">
                                                    <div class="col-md-9 add-tag-to-selected">
                                                            <span
                                                                class="badge badge-default tag-color-custom-contextmenu"
                                                                style="background: #e25858;">.</span>
                                                        <h5 class="text-capitalize"> high</h5>
                                                    </div>
                                                </div>
                                                <div @click="Add_Priority('2',null)"
                                                     class="users-select row m-0">
                                                    <div class="col-md-9 add-tag-to-selected">
                                                            <span
                                                                class="badge badge-default tag-color-custom-contextmenu"
                                                                style="background: #e58c62;">.</span>
                                                        <h5 class="text-capitalize">medium</h5>
                                                    </div>
                                                </div>
                                                <div @click="Add_Priority('1',null)"
                                                     class="users-select row m-0">
                                                    <div class="col-md-9 add-tag-to-selected">
                                                            <span
                                                                class="badge badge-default tag-color-custom-contextmenu"
                                                                style="background: #5987d1;">.</span>
                                                        <h5>Low</h5>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li v-if="selectedCards.types === 'task'">
                                <a @click="deleteAllTasks(currentColumnIndex, currentColumnKey, selectedCards.cardId)"
                                   href="javascript:void(0)">
                                    <i class="fad fa-eraser"></i>
                                    Remove Tasks From This Column
                                </a>
                            </li>

                            <li @click="openPicker()" style="position: relative">
                                <datepicker
                                    :disabled-dates="disabledDates"
                                    @selected="ActionToSelectedTask('','date')"
                                    calendar-button-icon='<i class="outline-event icon-image-preview"></i>'
                                    format='dd MMM'
                                    input-class="dateCal-selected"
                                    v-model="date_for_selected">
                                </datepicker>
                                <a class="calender li-opacity clickHide">
                                    <i class="fal fa-calendar-plus contex-menu-icon"></i>
                                    Set Due Date
                                </a>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addModal" role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-radius: 13px;">
                        <h5 class="modal-title">Add column</h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>You need to set a name and progress color for the new column.</p>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Name</label>
                            <div class="col-sm-10">
                                <input class="form-control" type="text" v-model="addField.name">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Percent Complete </label>
                            <div class="col-sm-8">
                                <select class="form-control" v-model="addField.progress">
                                    <option selected style="background-image:url('/images/0.png');" value="0"> 0%
                                        Complete
                                    </option>
                                    <option style="background-image:url('/images/125.png');" value="125">12.5%
                                        Complete
                                    </option>
                                    <option style="background-image:url('/images/25.png');" value="25">25% Complete
                                    </option>
                                    <option style="background-image:url('/images/375.png');" value="375">37.5%
                                        Complete
                                    </option>
                                    <option style="background-image:url('/images/50.png');" value="50">50% Complete
                                    </option>
                                    <option style="background-image:url('/images/625.png');" value="625">62.5%
                                        Complete
                                    </option>
                                    <option style="background-image:url('/images/75.png');" value="75">75% Complete
                                    </option>
                                    <option style="background-image:url('/images/875.png');" value="875">87.5%
                                        Complete
                                    </option>
                                    <option style="background-image:url('/images/100.png');" value="100">100% Complete
                                    </option>
                                </select>
                            </div>
                        </div>
                        <p class="text-danger" v-if="addField.error">{{addField.error}}</p>
                    </div>
                    <div class="modal-footer">
                        <button @click="setColumn()" class="btn save-all" type="button">Add Column</button>
                        <button @click="clearInputFeild()" class="btn btn-secondary ml-2" type="button">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="EditModal" role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-radius: 13px;">
                        <h5 class="modal-title">Update column</h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body bg-white">
                        <p>You need to set a progress and color for the new column.</p>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Name</label>
                            <div class="col-sm-10">
                                <input class="form-control" type="text" v-model="editField.name">
                                <span class="text-danger" v-if="editField.error">{{editField.error}}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Percent Complete</label>
                            <div class="col-sm-8">
                                <select class="form-control" v-model="editField.progress">
                                    <option style="background-image:url('/images/0.png');" value="0">0% Complete
                                    </option>
                                    <option style="background-image:url('/images/125.png');" value="125">12.5%
                                        Complete
                                    </option>
                                    <option style="background-image:url('/images/25.png');" value="25">25% Complete
                                    </option>
                                    <option style="background-image:url('/images/375.png');" value="375">37.5%
                                        Complete
                                    </option>
                                    <option style="background-image:url('/images/50.png');" value="50">50% Complete
                                    </option>
                                    <option style="background-image:url('/images/625.png');" value="625">62.5%
                                        Complete
                                    </option>
                                    <option style="background-image:url('/images/75.png');" value="75">75% Complete
                                    </option>
                                    <option style="background-image:url('/images/875.png');" value="875">87.5%
                                        Complete
                                    </option>
                                    <option style="background-image:url('/images/100.png');" value="100">100% Complete
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="updateColumn" class="btn save-all" type="button">Update</button>
                        <button @click="clearInputFeild" class="btn btn-secondary ml-2" type="button">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="TagManage" role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-radius: 13px;">
                        <h3 class="text-center text-uppercase">Manage All Tag</h3>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body bg-white">
                        <div class="table-responsive mgTagTable" id="table">
                            <table class="table" data-v-095ab3dc="">
                                <thead data-v-095ab3dc="">
                                <tr data-v-095ab3dc="">
                                    <th>Tag Title</th>
                                    <th>Color</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="tag in manageTag">
                                    <tr v-if="tag.title !== 'Dont Forget'">
                                        <td class="pt-3-half" v-if="tag.title === 'Dont Forget'">{{tag.title}}</td>
                                        <td @keydown="newLineoff($event)" @keyup="updateTagName($event,tag)"
                                            class="pt-3-half"
                                            contenteditable="true" v-else>
                                            {{tag.title}}
                                        </td>
                                        <td class="pt-3-half">
                                            <input :value="tag.color" @change="updateTagColor($event,tag)"
                                                   style="cursor: pointer;background-color: #fff;border: none;"
                                                   type="color">
                                        </td>
                                        <td>
                                            <a @click="DeleteTagFromModal(tag)"
                                               class="compltit-blue-a badge badge-danger"
                                               href="javascript:void(0)">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button aria-label="Close" class="btn btn-secondary " data-dismiss="modal" type="button">Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="transferCard" role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-radius: 13px;">
                        <h4 class="text-center ">Transfer Task To Another Board</h4>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body bg-white">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Select Board :</label>
                            <div class="col-sm-8">
                                <select @change="showSubBoard()" class="form-control" v-model="selectedBoard">
                                    <option disabled>Select Board</option>
                                    <option :key="index" v-bind:value="navs.id" v-for="(navs, index) in board"
                                            v-if="navs.type === 'board'"> {{navs.title}}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" v-if="subBoard.length > 0">
                            <label class="col-sm-4 col-form-label">Select Board List :</label>
                            <div class="col-sm-8">
                                <select @change="getColumn()" class="form-control" v-model="selectedSubBoard">
                                    <option disabled>Select Board List</option>
                                    <option :key="index" v-bind:value="navList.id" v-for="(navList, index) in subBoard">
                                        {{navList.board_title}}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" v-if="boardColumn.length > 0">
                            <label class="col-sm-4 col-form-label">Select Board Column :</label>
                            <div class="col-sm-8">
                                <select @change="getBttn()" class="form-control" v-model="selectedBoardColumn">
                                    <option disabled>Select Board Column</option>
                                    <option :key="index" v-bind:value="navList.id"
                                            v-for="(navList, index) in boardColumn">
                                        {{navList.title}}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="transferCardToOtherBoard" aria-label="Close" class="btn save-all"
                                data-dismiss="modal" type="button" v-if="transferBtn">Transfer
                        </button>
                        <button aria-label="Close" class="btn btn-secondary ml-2" data-dismiss="modal" type="button">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="transferColumn" role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-radius: 13px;">
                        <h4 class="text-center ">Transfer Column To Another Board </h4>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body bg-white">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Select Board :</label>
                            <div class="col-sm-8">
                                <select @change="showSubBoard()" class="form-control" v-model="selectedBoard">
                                    <option disabled>Select Board</option>
                                    <option :key="index" v-bind:value="navs.id" v-for="(navs, index) in board"
                                            v-if="navs.type === 'board'">{{navs.title}}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" v-if="subBoard.length > 0">
                            <label class="col-sm-4 col-form-label">Select Board List :</label>
                            <div class="col-sm-8">
                                <select @change="getBttn()" class="form-control" v-model="selectedSubBoard">
                                    <option disabled>Select Board List</option>
                                    <option :key="index" v-bind:value="navList.id" v-for="(navList, index) in subBoard">
                                        {{navList.board_title}}
                                    </option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button @click="transferColumnToOtherBoardSave" aria-label="Close" class="btn save-all"
                                data-dismiss="modal" type="button" v-if="transferBtn">Transfer
                        </button>
                        <button aria-label="Close" class="btn btn-secondary ml-2" data-dismiss="modal" type="button">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="listLinkColumn" role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-radius: 13px;">
                        <h4 class="text-center ">Link List To Column </h4>
                        
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Select Nav :</label>
                            <div class="col-sm-9">
                                <select @change="showSubNav()" class="form-control" v-model="selectedNav">
                                    <option disabled>Select Nav</option>
                                    <option :key="index" v-bind:value="navs.id" v-for="(navs, index) in nav" v-if="navs.type === 'list'">{{navs.title}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" v-if="subNav.length > 0">
                            <label class="col-sm-4 col-form-label">Select Nav List :</label>
                            <div class="col-sm-8">
                                <select @change="linkToCol()" class="form-control" v-model="selectedSubNav">
                                    <option disabled>Select Nav List</option>
                                    <option :key="index" v-bind:value="navList.id" v-for="(navList, index) in subNav">
                                        {{navList.list_title}}
                                    </option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button @click="listLinkToCol" aria-label="Close" class="btn save-all" data-dismiss="modal" type="button" v-if="linkBtn === 1">
                            Link
                        </button>

                        <button aria-label="Close" class="btn btn-secondary ml-2" data-dismiss="modal" type="button">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="loader" role="dialog"
             tabindex="-1">
            <div class="modal-dialog " role="document">
                <div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="user_list" role="dialog"
             tabindex="-1">
            <div class="modal-dialog " role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-radius: 13px;">
                        <h5 class="modal-title">User List</h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body list-model">
                        <div class="form-group row">
                            <div class="col-sm-9">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <label class="checkbox_cus_mini">
                                            <input @click="checkedAllUser(allUsers)" class="checkedAllUser"
                                                   type="checkbox"> All
                                            <span class="checkmark"></span>
                                        </label>
                                    </li>
                                    <li class="list-group-item" v-for="user in allUsers">
                                        <label class="checkbox_cus_mini">
                                            <input :value="user.id" @click="addUserToFilter(user.id)"
                                                   name="side_dav" type="checkbox" v-model="userIdList"> {{
                                            user.name }}
                                            <span class="checkmark"></span>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="userFilter()" class="btn save-all" type="button">Filter User</button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="priority_list_modal"
             role="dialog"
             tabindex="-1">
            <div class="modal-dialog " role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-radius: 13px;">
                        <h5 class="modal-title">Priority List </h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body list-model">
                        <div class="form-group row">
                            <div class="col-sm-9">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <label class="checkbox_cus_mini">
                                            <input @click="addFilterToFilter('3')" class="checkedUser" type="checkbox">
                                            High
                                            <span class="checkmark"></span>
                                        </label>
                                    </li>
                                    <li class="list-group-item">
                                        <label class="checkbox_cus_mini">
                                            <input @click="addFilterToFilter('2')" class="checkedUser" type="checkbox">
                                            Medium
                                            <span class="checkmark"></span>
                                        </label>
                                    </li>
                                    <li class="list-group-item">
                                        <label class="checkbox_cus_mini">
                                            <input @click="addFilterToFilter('1')" class="checkedUser" type="checkbox">
                                            Low
                                            <span class="checkmark"></span>
                                        </label>
                                    </li>
                                    <li class="list-group-item">
                                        <label class="checkbox_cus_mini">
                                            <input @click="addFilterToFilter('0')" class="checkedUser" type="checkbox">
                                            No Priority
                                            <span class="checkmark"></span>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="priorityHide()" class="btn btn-primary" type="button">Hide</button>&nbsp;
                        <button @click="priorityShow() " class="btn btn-primary ml-2" type="button">Show</button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalCenterTitle" class="modal fade" id="addExistingTask"
             role="dialog"
             tabindex="-1">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-radius: 13px;">
                        <h5 class="modal-title">Add Existing Task</h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body list-model">
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Select Nav :</label>
                            <div class="col-sm-9">
                                <select @change="showSubNav()" class="form-control" v-model="selectedNav">
                                    <option disabled>Select Nav</option>
                                    <option :key="index" v-bind:value="navs.id" v-for="(navs, index) in nav"
                                            v-if="navs.type === 'list'">{{navs.title}}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" v-if="subNav.length > 0">
                            <label class="col-sm-4 col-form-label">Select Nav List :</label>
                            <div class="col-sm-8">
                                <select @change="getAllTask()" class="form-control" v-model="selectedSubNav">
                                    <option disabled>Select Nav List</option>
                                    <option :key="index" v-bind:value="navList.id" v-for="(navList, index) in subNav">
                                        {{navList.list_title}}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush">
                            <div v-if="tree4data.length > 0">
                                <label class="checkbox_cus_mini">
                                    <input @change="selectAll()" class="checkedAll" name="side_dav" type="checkbox"> All
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div v-for="tree in tree4data">
                                <li :class="(tree.board_parent_id !== null && tree.children.length <= 0) ? 'list-group-item-hide' : ''"
                                    class="list-group-item">
                                    <label
                                        :class="{'checkbox_cus_mini' : true, '_pen_disable' : true,'disabledTask': tree.board_parent_id !== null}"
                                        v-html="tree.text"
                                        v-if="tree.text !== '' && tree.text !== 'Dont Forget Section' && tree.board_parent_id !== null && tree.children.length > 0">
                                    </label>
                                    <label class="checkbox_cus_mini"
                                           v-if="tree.text !== '' && tree.text !== 'Dont Forget Section' && tree.board_parent_id == null">
                                        <input :class="{'selectAll': true}" :id="tree.id" :value="tree.id"
                                               @change="selectChild(tree.id)"
                                               type="checkbox" v-if="tree.text !== '' && tree.board_parent_id === null"
                                               v-model="selectedExistedTask">
                                        <span v-html="tree.text"></span>
                                        <span class="checkmark"></span>
                                    </label>
                                    <ul class="list-group list-group-flush" v-if="tree.children">
                                        <div
                                            :class="(child.board_parent_id !== null && child.children.length <= 0) ? 'list-group-item-hide' : ''"
                                            v-for="child in tree.children">
                                            <li class="list-group-item">
                                                <label
                                                    :class="{'checkbox_cus_mini' : true, '_pen_disable' : true,'disabledTask': child.board_parent_id !== null}"
                                                    v-if="child.text !== '' && child.text !== 'Dont Forget Section' && child.board_parent_id !== null && child.children.length > 0">
                                                    <input :id="child.id" :value="child.id" checked
                                                           class="tree-child"
                                                           disable type="checkbox"> <span v-html="child.text"></span>
                                                </label>
                                                <label class="checkbox_cus_mini"
                                                       v-if="child.text !== '' && child.text !== 'Dont Forget Section' && child.board_parent_id == null">
                                                    <input :id="child.id" :value="child.id"
                                                           @change="selectChild(child.id)"
                                                           class="tree-child selectAll" type="checkbox"
                                                           v-model="selectedExistedTask"> <span
                                                    v-html="child.text"></span>
                                                    <span class="checkmark"></span>
                                                </label>

                                                <ul class="list-group list-group-flush" v-if="child.children">
                                                    <div
                                                        :class="(child1.board_parent_id !== null && child1.children.length <= 0) ? 'list-group-item-hide' : ''"
                                                        v-for="child1 in child.children">
                                                        <li class="list-group-item">
                                                            <label
                                                                :class="{'checkbox_cus_mini' : true, '_pen_disable' : true,'disabledTask': child1.board_parent_id !== null}"
                                                                v-if="child1.text !== '' && child1.text !== 'Dont Forget Section' && child1.board_parent_id !== null  && child1.children.length > 0">
                                                                <input :id="child1.id" :value="child1.id"
                                                                       checked
                                                                       class="tree-child"
                                                                       disable type="checkbox"> <span
                                                                v-html="child1.text"></span>
                                                            </label>

                                                            <label class="checkbox_cus_mini"
                                                                   v-if="child1.text !== '' && child1.text !== 'Dont Forget Section' && child1.board_parent_id == null">
                                                                <input :id="child1.id" :value="child1.id"
                                                                       @change="selectChild(child1.id)"
                                                                       class="tree-child selectAll" type="checkbox"
                                                                       v-model="selectedExistedTask"><span
                                                                v-html="child1.text"></span>
                                                                <span class="checkmark"></span>
                                                            </label>
                                                            <ul class="list-group list-group-flush"
                                                                v-if="child1.children">
                                                                <div
                                                                    :class="(child2.board_parent_id !== null && child2.children.length <= 0) ? 'list-group-item-hide' : ''"
                                                                    v-for="child2 in child1.children">
                                                                    <li class="list-group-item">
                                                                        <label
                                                                            :class="{'checkbox_cus_mini' : true, '_pen_disable' : true,'disabledTask': child2.board_parent_id !== null}"
                                                                            v-if="child2.text !== '' && child2.text !== 'Dont Forget Section' && child2.board_parent_id !== null  && child2.children.length > 0">
                                                                            <input :id="child2.id" :value="child2.id"
                                                                                   checked
                                                                                   class="tree-child"
                                                                                   disable type="checkbox"> <span
                                                                            v-html="child2.text"></span>
                                                                        </label>

                                                                        <label class="checkbox_cus_mini"
                                                                               v-if="child2.text !== '' && child2.text !== 'Dont Forget Section' && child2.board_parent_id == null">
                                                                            <input :id="child2.id" :value="child2.id"
                                                                                   @change="selectChild(child2.id)"
                                                                                   class="tree-child selectAll"
                                                                                   type="checkbox"
                                                                                   v-model="selectedExistedTask">
                                                                            <span v-html="child2.text"></span>
                                                                            <span class="checkmark"></span>
                                                                        </label>

                                                                        <ul class="list-group list-group-flush"
                                                                            v-if="child2.children">
                                                                            <div
                                                                                :class="(child3.board_parent_id !== null && child3.children.length <= 0) ? 'list-group-item-hide' : ''"
                                                                                v-for="child3 in child2.children">
                                                                                <li class="list-group-item">
                                                                                    <label
                                                                                        :class="{'checkbox_cus_mini' : true, '_pen_disable' : true,'disabledTask': child2.board_parent_id !== null}"
                                                                                        v-if="child3.text !== '' && child3.text !== 'Dont Forget Section' && child3.board_parent_id !== null && child3.children.length > 0">
                                                                                        <input :id="child3.id"
                                                                                               :value="child3.id"
                                                                                               checked
                                                                                               class="tree-child"
                                                                                               disable type="checkbox">
                                                                                        <span
                                                                                            v-html="child3.text"></span>
                                                                                    </label>

                                                                                    <label class="checkbox_cus_mini"
                                                                                           v-if="child3.text !== '' && child3.text !== 'Dont Forget Section' && child3.board_parent_id == null">
                                                                                        <input :id="child3.id"
                                                                                               :value="child3.id"
                                                                                               @change="selectChild(child3.id)"
                                                                                               class="tree-child selectAll"
                                                                                               type="checkbox"
                                                                                               v-model="selectedExistedTask">
                                                                                        <span
                                                                                            v-html="child3.text"></span>
                                                                                        <span class="checkmark"></span>
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
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button @click="AddExistingTasks" class="btn save-all" type="button">Add Tasks</button>&nbsp;
                        <button @click="clearInputFeild" class="btn btn-secondary ml-2" type="button">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="detailsShowFull" id="details">
            <TaskDetails :selectedData="selectedData"
                         @textArea="ShowTextArea"
                         v-if="Object.keys(selectedData).length > 0">
            </TaskDetails>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="updateListBoardModel"
             role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title pl-3"> Update {{list.name}} <span
                            class="text-uppercase">[{{list.type}}]</span></h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Title</label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" v-model="list.name">
                                <span class="text-danger" v-if="boardListErrors.name">{{ boardListErrors.name[0] }}</span>
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
                        <button @click="UpdateListOrBoard" class="btn btn-primary ladda-button ladda_update_list_board" data-style="expand-right">
                            Update
                        </button>
                        <button aria-label="Close" class="btn btn-secondary ml-2" data-dismiss="modal" type="button">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="transToNav" role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-radius: 13px;">
                        <h4 class="text-center ">Move <span v-if="type_T === 'board'">Board</span> <span
                            v-else>List</span></h4>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Select Board Nav :</label>
                            <div class="col-sm-8">
                                <select @change="get_T_Bttn()" class="form-control" v-model="selectedListNav">
                                    <option disabled value="Select list Nav">Select <span v-if="type_T === 'board'">Board</span>
                                        <span v-else>List</span> Nav
                                    </option>
                                    <option :disabled="((navs.id !== nav_id) ? false : true)" :key="index"
                                            v-bind:value="navs.id"
                                            v-for="(navs, index) in nav_T"
                                            v-if="navs.type === type_T">{{navs.title}}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="MoveAllTask" aria-label="Close" class="btn btn-danger"
                                data-dismiss="modal" type="button" v-if="transferBtn">Move Board
                        </button>
                        <button aria-label="Close" class="btn btn-secondary ml-2" data-dismiss="modal" type="button">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="transAndMoveTAsk"
             role="dialog"
             tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-radius: 13px;">
                        <h4 class="text-center ">Delete And Move <span v-if="type_T === 'board'">Card</span> <span
                            v-else>Task</span>
                            To Another <span v-if="type_T === 'board'">Board</span> <span v-else>List</span></h4>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Select <span v-if="type_T === 'board'">Board</span>
                                <span v-else>List</span> Nav :</label>
                            <div class="col-sm-8">
                                <select @change="showSubList_T" class="form-control" v-model="selectedListNav">
                                    <option disabled value="Select list Nav">Select <span v-if="type_T === 'board'">Board</span>
                                        <span v-else>List</span> Nav
                                    </option>
                                    <option :key="index" v-bind:value="navs.id" v-for="(navs, index) in nav_T"
                                            v-if="navs.type === type_T">{{navs.title}}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" v-if="list_T.length > 0">
                            <label class="col-sm-4 col-form-label">Select <span v-if="type_T === 'board'">Board</span>
                                <span v-else>List</span> :</label>
                            <div class="col-sm-8">
                                <select @change="get_T_Bttn()" class="form-control" v-model="selectedSubList">
                                    <option disabled value="Select list">Select <span
                                        v-if="type_T === 'board'">Board</span> <span v-else>List</span></option>
                                    <option :disabled="((navList.id !== board_id) ? false : true)" :key="index"
                                            v-bind:value="navList.id"
                                            v-for="(navList, index) in list_T">
                                        <span v-if="type_T === 'board'">{{navList.board_title}}</span> <span v-else>{{navList.list_title}}</span>
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="DeleteAndMoveAllTask" aria-label="Close" class="btn btn-danger"
                                data-dismiss="modal" type="button" v-if="transferBtn">Delete & Move All <span
                            v-if="type_T === 'board'">Card</span> <span v-else>Task</span>
                        </button>
                        <button aria-label="Close" class="btn btn-secondary ml-2" data-dismiss="modal" type="button">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="description-model"
             role="dialog"
             tabindex="-1">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-radius: 13px;">
                        <h3 class="text-center ">{{ selectedData.data }} <br></h3>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="pl-3">
                        <h4 style="text-decoration-line: underline">Description</h4>
                    </div>
                    <div class="modal-body list-model">
                        <div class="form-group row">
                            <div class="col-sm-12">
                                   <textarea id="editor-modal" name="editor"
                                             row="50" v-model="selectedData.description">
                                            This is my textarea to be replaced with CKEditor.
                                        </textarea>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</template>
<script>
    import flatPickr from 'vue-flatpickr-component';
    import 'flatpickr/dist/flatpickr.css';
    import switches from 'vue-switches';
    import hotkeys from 'hotkeys-js';
    import ClickOutside from 'vue-click-outside';
    import Datepicker from 'vuejs-datepicker';
    import {Container, Draggable} from 'vue-smooth-dnd';
    import {applyDrag, generateItems} from '../../../../assets/plugins/utils/helpers';
    import VueTagsInput from '@johmun/vue-tags-input';
    import TaskDetails from "../boardCardDetails";
    import Swal from 'sweetalert2';
    import * as Ladda from "ladda";
    import CKEditor from '@ckeditor/ckeditor5-vue';

    export default {
        components: {
            Container,
            Draggable,
            flatPickr,
            switches,
            VueTagsInput,
            Datepicker,
            TaskDetails,
            ckeditor: CKEditor.component
        },
        data() {
            return {
                baseUrl: window.location.origin,
                id: 0,
                tags: [],
                addField: {
                    name: null,
                    color: null,
                    error: null,
                    progress: 0
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
                    altFormat: 'd M Y',
                    dateFormat: 'd M Y',
                },
                date_for_selected: null,
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
                linkBtn: 0,
                project: null,
                tree4data: [],
                currentColumn: null,
                currentColumnIndex: null,
                currentColumnKey: null,
                cards: [],
                selectedCards: [],
                scene: {},
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
                    nav_id: null,
                    type: 'board'
                },
                navItem: {
                    title: null,
                    type: null,
                    sort_id: null,
                    project_id: null,
                },
                selectedData: {},
                selectedIds: [],
                task_logs: null,
                check_uncheck_child: null,
                manageTag: null,
                allUsers: null,
                allTags: [],
                disabledDates: {
                    id: null,
                },
                shift_first: null,
                triggers: null,
                tagTriggers: null,
                userNames: '',
                projectUsers: null,
                commentsData: null,
                filter_types: null,
                selectedPriorites: [],
                userIdList: [],
                Socket: null,
                authUser: null,
                userSeggistion: null,
                searchTag: null,
                searchAllTags: null,
                copyOrCut: null,
                allTeamsUsers: [],
                copySelectedId: [],
                auth_user: null,
                filter_type: null,
                nav_id: null,
                board_id: null,
                projectId: null,
                nav_T: [],
                list_T: [],
                column_T: [],
                action_T: '',
                type_T: '',
                selectedListNav: 'Select List Nav',
                selectedSubList: 'Select List',
                selectedColumn: 'Select column',
                delete_popup: 0,
                editorsDesc: null,
                filterTag: 0,
                boardListErrors: [],
            }
        },
        mounted() {
            var _this = this;
            _this.authUser = _this.auth_user;
            _this.$toastr.defaultTimeout = 1000;
            _this.$toastr.defaultPreventDuplicates = true;
            _this.projectId = _this.$route.params.projectId;
            _this.board_id = _this.$route.params.id;
            let token = Spark.csrfToken;
            var nav_type = JSON.parse(localStorage.selected_nav);
            _this.nav_id = (_this.$route.params.nav_id !== undefined) ? _this.$route.params.nav_id : nav_type.nav_id;
            _this.list.name = (_this.$route.params.title !== undefined) ? _this.$route.params.title : nav_type.title;
            _this.list.description = (_this.$route.params.description !== undefined) ? _this.$route.params.description : nav_type.description;
            _this.list.nav_id = (_this.$route.params.nav_id !== undefined) ? _this.$route.params.nav_id : nav_type.nav_id;

            $('#header-item').text('Project / Task Board');
            $(document)
                .one('focus.autoExpand', 'textarea.autoExpand', function () {
                    var savedValue = this.value;
                    this.value = '';
                    this.baseScrollHeight = this.scrollHeight;
                    this.value = savedValue;
                })
                .on('input.autoExpand', 'textarea.autoExpand', function () {
                    var minRows = this.getAttribute('data-min-rows') | 0, rows;
                    this.rows = minRows;
                    rows = Math.ceil((this.scrollHeight - this.baseScrollHeight) / 16);
                    this.rows = minRows + rows;
                });

            $(document).ready(function () {
                $('[data-toggle="popover"]').popover();

                $("#popoverData").popover({trigger: "hover"});

                $('#loder-hide').fadeIn();

                $('.searchList').hide();
            });

            Bus.$on('detailsUpdate', function (data) {
                _this.selectedData.description = data.description;
                _this.getBoardTask();
            });

            Bus.$on('FilterActionInBoard', function (data) {
                _this.GetFilterData(data.type);
            });

            Bus.$on('MoveListTOAnotherNav', function (data) {
                _this.MoveListTOAnotherNav(data);
            });

            Bus.$on('DeleteBoard', function (data) {
                _this.DeleteListOrBoard(data);
            });


            Bus.$emit('AddedFilterSuccess');
            //_this.getBoardTask();
            // _this.getAuthUser();
            _this.allTeamUsers();
            _this.connectSocket();
            setTimeout(function () {
                _this.ckeditFileUpload();
            }, 1000);
        },
        created() {
            let _this = this;
            hotkeys('tab,shift+tab,up,down,left,right,ctrl+c,ctrl+x,ctrl+v,ctrl+m,delete,ctrl+u,ctrl+b,ctrl+s,ctrl+i,shift+3,shift+2', function (event, handler) {
                event.preventDefault();
                switch (handler.key) {
                    case "up" :
                        _this.moveCard('up');
                        break;
                    case "down" :
                        _this.moveCard('down');
                        break;
                    case "left" :
                        _this.HideDetails();
                        break;
                    case "right" :
                        if (_this.selectedIds.length >= 1) {
                            _this.ShowDetails();
                        }
                        break;
                    case "ctrl+c":
                        _this.copySelectedId = _this.selectedIds;
                        _this.copyOrCut = 'copy';
                        _this.$toastr.s("Copy Task success !");
                        break;
                    case "ctrl+x":
                        _this.copySelectedId = _this.selectedIds;
                        _this.copyOrCut = 'cut';
                        _this.$toastr.s("Cut Task success !");
                        break;
                    case "ctrl+v":
                        _this.pastCopyAndCut();
                        break;
                    case "delete":
                        var nav_type = JSON.parse(localStorage.selected_nav);
                        if (nav_type.type === 'board' && _this.selectedIds.length > 0) {
                            _this.deleteSelectedTask();
                        }
                        break;
                    case "ctrl+u":
                        _this.shwAssignUserDropDown();
                        break;
                    case "ctrl+m":
                        if (_this.selectedIds.length > 0) {
                            _this.showTransferModel(_this.currentColumn, '', '', _this.currentColumnIndex);
                        }
                        break;
                    case "ctrl+b":
                        break;
                    case "ctrl+s":
                        break;
                    case "ctrl+i":
                        break;
                    case "shift+3":
                        _this.filterTag = 1;
                        _this.showtagBox();
                        break;
                    case "shift+2":
                        _this.filterTag = 0;
                        _this.showtagBox();
                        break;
                }
            });
        },
        methods: {
            connectSocket: function () {
                let app = this;
                if (app.Socket === null) {
                    app.Socket = io.connect(window.socket_url);

                    app.Socket.on('CardMoved', function (res) {
                        if (res.project_id === app.projectId && res.user_id !== app.authUser.id){
                            app.getBoardTask();
                        }
                    });
                    app.Socket.on('taskUpdateSocket', function (res) {
                        if (res.board_id === app.board_id && res.project_id === app.projectId && res.user_id !== app.authUser.id) {
                            app.getBoardTask();
                        }
                    });
                    app.Socket.on('cardUpdatedSocket', function (res) {
                        if (res.board_id === app.board_id && res.project_id === app.projectId && res.user_id !== app.authUser.id) {
                        }
                        app.getBoardTask();
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
            getData() {
                this.HideDetails();
                this.scene = {
                    type: 'container',
                    props: {
                        orientation: 'horizontal'
                    },
                    children: generateItems(this.cards.length, i => ({
                        id: `column${i}`,
                        boardId: this.cards[i].id,
                        type: 'container',
                        name: this.cards[i].column,
                        ruleType: this.cards[i].type,
                        ruleStatus: this.cards[i].status,
                        usersName: this.cards[i].users,
                        moveToCol: this.cards[i].moveToCol,
                        ruleName: this.cards[i].ruleName,
                        boardName: this.cards[i].boardName,
                        moveToColName: this.cards[i].colName,
                        progress: this.cards[i].progress,
                        linkToList: this.cards[i].linkToList,
                        total_card: this.cards[i].total_card,
                        props: {
                            orientation: 'vertical',
                            className: 'card-container'
                        },
                        hidden: this.cards[i].hidden,
                        children: generateItems(this.cards[i].task.length, j => ({
                            type: 'draggable',
                            id: `${i}${j}`,
                            cardId: this.cards[i].task[j].id,
                            types: this.cards[i].task[j].type,
                            assigned_user: this.cards[i].task[j].assigned_user,
                            assigned_user_ids: this.cards[i].task[j].assigned_user_ids,
                            users: this.cards[i].task[j].users,
                            existing_tags: this.cards[i].task[j].existing_tags,
                            props: {
                                className: 'card',
                                style: {backgroundColor: 'white'}
                            },
                            child: this.cards[i].task[j].child,
                            childrens: this.cards[i].task[j].children,
                            parents: this.cards[i].task[j].parents,
                            comment: this.cards[i].task[j].comment,
                            files: '',
                            userName: this.cards[i].task[j].userName,
                            data: this.cards[i].task[j].name,
                            list_data: this.cards[i].task[j].list,
                            open: this.cards[i].task[j].cardOpen,
                            textareaShow: this.cards[i].task[j].textareaShow,
                            description: this.cards[i].task[j].description,
                            list_id: this.cards[i].task[j].list_id,
                            board_id: this.cards[i].task[j].multiple_board_id,
                            date: this.cards[i].task[j].date,
                            parent_id: this.cards[i].task[j].parent_id,
                            progress: this.cards[i].task[j].progress,
                            created_by: this.cards[i].task[j].created_by,
                            tags: this.cards[i].task[j].tags,
                            tagTooltip: this.cards[i].task[j].tagTooltip,
                            delete: this.cards[i].task[j].name,
                            priority_label: this.cards[i].task[j].priority_label
                        }))
                    })),
                };
                $('[data-toggle="tooltip"]').tooltip('dispose');
                $('#loder-hide').fadeOut();
                setTimeout(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                }, 1000)
            },
            selectChild(id) {
                var _this = this;
                _this.findChild(id, _this.tree4data);
                var is_checked = _this.selectedExistedTask.indexOf(id);
                if (is_checked > -1) {
                    _this.CheckWithChild(id, _this.check_uncheck_child);
                } else {
                    _this.UncheckWithChild(id, _this.check_uncheck_child);
                }
            },
            findChild(id, data) {
                if (data.length > 0) {
                    for (let index = 0; index < data.length; index++) {
                        if (index !== undefined && data[index].id === id) {
                            if (data[index].board_parent_id === null) {
                                this.check_uncheck_child = data[index].children;
                            }
                            return true;
                        } else {
                            this.findChild(id, data[index].children);
                        }
                    }
                }
            },
            CheckWithChild(id, child) {
                var _this = this;
                if (id !== 0 && _this.selectedExistedTask.indexOf(id) === -1) {
                    _this.selectedExistedTask.push(id);
                }
                if (child.length > 0) {
                    for (let index = 0; index < child.length; index++) {
                        if (child[index].board_parent_id === null) {
                            _this.CheckWithChild(child[index].id, child[index].children);
                        } else {
                            _this.CheckWithChild(0, child[index].children);
                        }
                    }
                }
            },
            UncheckWithChild(id, child) {
                var _this = this;
                var key = _this.selectedExistedTask.indexOf(id);
                if (key !== -1) {
                    _this.selectedExistedTask.splice(key, 1);
                }

                if (child.length > 0) {
                    for (let index = 0; index < child.length; index++) {
                        _this.UncheckWithChild(child[index].id, child[index].children);
                    }
                }
            },
            hideChilds(cardId) {
                let _this = this;
                $('#loder-hide').fadeIn();
                let data = {
                    'parent_id': cardId
                };
                axios.post('/api/hideChildes', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.getBoardTask();
                        _this.boardSocketCall();
                        $('#loder-hide').fadeOut();
                    })
                    .catch(error => {
                    })
            },
            selectAll() {
                if ($('.checkedAll').prop('checked') === false) {
                    $('.selectAll').prop('checked', false);
                    this.selectedExistedTask = [];
                } else {
                    $('.selectAll').prop('checked', true);

                    for (let index = 0; index < this.tree4data.length; index++) {
                        if (this.tree4data[index].board_parent_id === null) {
                            this.selectedExistedTask.push(this.tree4data[index].id);
                        }
                        this.recursive(this.tree4data[index].children, this.tree4data[index].id);
                    }
                }

            },
            recursive(child, parent_id) {
                for (let index = 0; index < child.length; index++) {
                    let key = this.selectedExistedTask.indexOf(child[index].id);
                    let parentKey = this.selectedExistedTask.indexOf(parent_id);
                    if (key !== -1 && parentKey === -1) {
                        this.selectedExistedTask.splice(key, 1);
                    } else {
                        if (key === -1 && child[index].board_parent_id === null) {
                            this.selectedExistedTask.push(child[index].id);
                        }
                    }
                    if (child[index].children.length > 0) {
                        this.recursive(child[index].children, child[index].id);
                    }
                }
            },
            onColumnDrop(dropResult) {
                const scene = Object.assign({}, this.scene);
                scene.children = applyDrag(scene.children, dropResult);
                this.scene = scene;
                let data = scene;
                $('#loader').modal('show');
                axios.post('/api/column-sort', data)
                    .then(response => response.data)
                    .then(response => {
                        setTimeout(() => {
                            $('#loader').modal('hide');
                        }, 500);
                    })
                    .catch(error => {
                        setTimeout(() => {
                            $('#loader').modal('hide');
                        }, 500);
                        console.log('sorting failed');
                    });
                this.boardSocketCall();
            },
            onCardDrop(columnId, boardId, index, dropResult) {
                let _this = this;
                if (dropResult.removedIndex !== null || dropResult.addedIndex !== null) {
                    const scene = Object.assign({}, this.scene);
                    const column = scene.children.filter(p => p.id === columnId)[0];
                    const columnIndex = scene.children.indexOf(column);
                    const newColumn = Object.assign({}, column);
                    newColumn.children = applyDrag(newColumn.children, dropResult);
                    scene.children.splice(columnIndex, 1, newColumn);
                    this.scene = scene;
                    let data = this.scene.children[index];
                    axios.post('/api/card-sort', data)
                        .then(response => response.data)
                        .then(response => {
                            _this.getBoardTask();
                            setTimeout(() => {
                            }, 500);
                        })
                        .catch(error => {
                        });

                    if (dropResult.removedIndex === null && dropResult.addedIndex !== null) {
                        let data = {
                            'id': dropResult.payload.cardId,
                            'board_parent_id': boardId
                        };
                        if (_this.selectedIds.length > 0) {
                            data.id = _this.selectedIds;
                        }

                        $('#loader').modal('show');
                        axios.post('/api/change-board-parent', data)
                            .then(response => response.data)
                            .then(response => {
                                setTimeout(() => {
                                    $('#loader').modal('hide');
                                }, 500);

                                _this.$toastr.s("Card successfully Moved ");
                                _this.Socket.emit('notification-update', response.users)
                            })
                            .catch(error => {
                                setTimeout(() => {
                                    $('#loader').modal('hide');
                                }, 500);
                            });
                    } else {
                    }
                    _this.boardSocketCall();
                }
            },
            onDragStart(columnId, boardId, index, dropResult) {
                if (dropResult.addedIndex != null) {
                }
            },
            getCardPayload(columnId) {
                return index => {
                    return this.scene.children.filter(p => p.id === columnId)[0].children[index]
                }
            },
            dragStart(dragResult) {
                let _this = this;
                let dragCardId = dragResult.payload.cardId;
                if (!_this.selectedIds.includes(dragCardId)) {
                    $('.card-list').removeClass('selected-card');
                    $('#card_' + dragCardId).addClass('selected-card');
                    _this.selectedIds = [dragCardId];
                }
            },

            addColumn() {
                $("#addModal").modal('show');
            },

            setColumn() {
                if (!this.addField.name) {
                    this.addField.error = 'Name is required!';
                } else if (!this.nav_id || !this.board_id) {
                    this.addField.error = 'select board';
                } else {
                    $("#addModal").modal('hide');
                    let data = {
                        title: this.addField.name,
                        color: this.addField.color,
                        progress: this.addField.progress,
                        project_id: this.projectId,
                        nav_id: this.nav_id,
                        multiple_board_id: this.board_id,
                        task: [{name: '', date: '', tags: [], clicked: 0}]
                    };
                    this.saveBoard(data);
                    this.getData();
                    this.addField = {};
                    this.addField.name = null;
                    this.addField.progress = 0;
                }
            },
            saveBoard(data) {
                let _this = this;
                axios.post('/api/board-save', data)
                    .then(response => response.data)
                    .then(response => {
                        if (response.success === true) {
                            _this.editField.progress = response.data.progress;
                            _this.cards.push({
                                id: response.data.id,
                                column: response.data.title,
                                progress: response.data.progress,
                                hidden: response.data.hidden,
                                task: []
                            });
                            _this.getBoardTask();
                            _this.$toastr.s("Board successfully created ");
                            setTimeout(() => {
                                Swal.close();
                            }, 1000);
                        }
                        _this.boardSocketCall();
                    })
                    .catch(error => {
                        console.log('Api not working.')
                    });
            },
            updateColumSow(index) {
                this.updateIndex = index;
                this.editField.name = this.cards[index].column;
                this.editField.boardId = this.cards[index].id;
                this.editField.progress = this.cards[index].progress;
                this.editField.color = this.cards[index].color;
                this.editField.error = '';

                setTimeout(function () {
                    $("#EditModal").modal('show');
                }, 100);
            },
            updateColumn() {
                let _this = this;
                if (!this.editField.name || this.editField.name === '') {
                    this.editField.error = 'Name is required!';
                } else {
                    let data = this.editField;
                    $("#EditModal").modal('hide');
                    axios.post('/api/board-modify', data)
                        .then(response => response.data)
                        .then(response => {
                            if (response.success === true) {
                                _this.cards[_this.updateIndex].column = _this.editField.name;
                                _this.cards[_this.updateIndex].progress = _this.editField.progress;
                                _this.$toastr.s("Column successfully updated ");
                            }
                        })
                        .catch(error => {
                        });
                    setTimeout(function () {
                        _this.getBoardTask();
                        _this.boardSocketCall();
                    }, 300);
                }
            },
            addExistingTask(index, id) {
                this.tree4data = [];
                this.subNav = [];
                this.selectedNav = 'Select Nav';
                this.selectedSubNav = 'Select Nav List';
                this.selectedExistedTask = [];
                this.currentColumn = id;
                this.currentColumnIndex = index;
                let _this = this;
                axios.get('/api/nav-item/' + this.projectId)
                    .then(response => response.data)
                    .then(response => {
                        _this.nav = response.success;
                    })
                    .catch(error => {
                    });
                this.updateIndex = index;
                $("#addExistingTask").modal('show');
            },
            showSubNav() {
                this.tree4data = [];
                let _this = this;
                let data = {
                    'projectId': this.projectId,
                    'navId': this.selectedNav
                };
                axios.post('/api/nav-list', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.subNav = response.success;
                    })
                    .catch(error => {
                    });
            },
            showTransferModel(index, key, cardId, id) {
                $('.jquery-accordion-menu').hide();
                this.board = [];
                this.subBoard = [];
                this.boardColumn = [];
                this.selectedBoard = 'Select Board';
                this.selectedSubBoard = 'Select Board List';
                this.selectedExistedTask = [];
                this.transferBtn = false;
                this.currentColumn = id;
                this.currentColumnIndex = index;
                let _this = this;
                axios.get('/api/nav-item/' + this.projectId)
                    .then(response => response.data)
                    .then(response => {

                        _this.board = response.success;

                        setTimeout(() => {
                            $('#transferCard').modal('show');
                        }, 500);
                    })
                    .catch(error => {

                    });
                this.updateIndex = index;
            },
            showLinkModel(index, boardId) {
                this.nav = [];
                this.subNav = [];
                this.selectedNav = 'Select Nav';
                this.selectedSubNav = 'Select Nav List';
                this.selectedExistedTask = [];
                this.currentColumn = boardId;
                this.currentColumnIndex = index;
                let _this = this;
                this.linkBtn = 0;
                axios.get('/api/nav-item/' + this.projectId)
                    .then(response => response.data)
                    .then(response => {

                        _this.nav = response.success;

                        setTimeout(() => {
                            $('#listLinkColumn').modal('show');
                        }, 500);
                    })
                    .catch(error => {

                    });
                this.updateIndex = index;

            },
            linkToCol() {
                let _this = this;
                let data = {
                    'projectId': this.projectId,
                    'columnId': this.currentColumn,
                    'multiple_list': this.selectedSubNav
                };
                axios.post('/api/is-linked', data)
                    .then(response => response.data)
                    .then(response => {
                        if (response.success === false) {
                            this.linkBtn = 1;
                        } else {
                            _this.$toastr.w("This list is already linked. Need to unlink first");
                            _this.linkBtn = 0;
                            setTimeout(() => {
                                Swal.close();
                            }, 1000);
                        }
                    })
                    .catch(error => {
                        console.log('not added');
                    });
            },
            listLinkToCol() {
                let _this = this;

                let data = {
                    'projectId': this.projectId,
                    'columnId': this.currentColumn,
                    'multiple_list': this.selectedSubNav
                };
                axios.post('/api/link-list-to-column', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.getBoardTask();
                        _this.boardSocketCall();
                        _this.$toastr.s("List successfully linked!");
                    })
                    .catch(error => {
                    });
            },
            unlinklistToCol(index, boardId, linkListId) {
                let _this = this;
                Swal.fire({
                    title: 'Are you sure to unlink?',
                    text: "",
                    type: 'warning',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "Yes, unlink it!"
                }).then((result) => {
                    if (result.value) {
                        let data = {
                            'linkListId': linkListId,
                            'projectId': _this.projectId,
                            'columnId': boardId,
                            'multiple_list': _this.selectedSubNav
                        };
                        axios.post('/api/unlink-list-to-column', data)
                            .then(response => response.data)
                            .then(response => {
                                _this.getBoardTask();
                                _this.boardSocketCall();
                                _this.$toastr.s("Successfully Unlinked");
                                setTimeout(() => {
                                    Swal.close();
                                }, 1000);
                            })
                            .catch(error => {
                                console.log('not added');
                            });
                    }
                })
            },
            showSubBoard() {
                let _this = this;
                this.subBoard = [];
                this.boardColumn = [];
                this.transferBtn = false;
                this.selectedSubBoard = 'Select Board List';
                let data = {
                    'projectId': this.projectId,
                    'boardId': this.selectedBoard
                };
                axios.post('/api/board-list', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.subBoard = response.success;

                    })
                    .catch(error => {
                    });
            },
            getColumn() {
                let _this = this;
                this.selectedBoardColumn = 'Select Board Column';
                this.transferBtn = false;
                let data = {
                    id: this.projectId,
                    nav_id: this.selectedBoard,
                    list_id: this.selectedSubBoard,
                };

                axios.post('/api/board-column', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.boardColumn = response.data;
                    })
                    .catch(error => {
                    });
            },
            getBttn() {
                this.transferBtn = true;
            },
            transferCardToOtherBoard() {
                var _this = this;
                let data = {
                    'cardId': this.selectedIds,
                    'board_parent_id': this.selectedBoardColumn,
                };
                axios.post('/api/Transfer-to-board', data)
                    .then(response => response.data)
                    .then(response => {
                        if (response.success) {
                            _this.getBoardTask();
                            _this.boardSocketCall();
                            $('#transferCard').modal('hide');
                            _this.$toastr.s("Card Successfully Transferred");
                        } else {
                            $('#transferCard').modal('hide');
                        }
                    })
                    .catch(error => {

                    });
            },
            transferColumnToOtherBoard(index, id) {

                this.board = [];
                this.subBoard = [];
                this.boardColumn = [];
                this.selectedBoard = 'Select Board';
                this.selectedSubBoard = 'Select Board List';
                this.selectedExistedTask = [];
                this.transferBtn = false;
                this.currentColumn = id;
                this.currentColumnIndex = index;
                let _this = this;
                axios.get('/api/nav-item/' + this.projectId)
                    .then(response => response.data)
                    .then(response => {
                        _this.board = response.success;
                        _this.$toastr.s("Column Successfully Transferred");
                        setTimeout(() => {
                            $('#transferColumn').modal('show');
                        }, 500);
                    })
                    .catch(error => {

                    });
                this.updateIndex = index;
            },
            transferColumnToOtherBoardSave() {
                var _this = this;

                let data = {
                    'columnId': this.currentColumn,
                    'multiple_board_id': this.selectedSubBoard,
                };
                axios.post('/api/Transfer-column-to-board', data)
                    .then(response => response.data)
                    .then(response => {

                        if (response.success) {
                            _this.getBoardTask();
                            _this.boardSocketCall();
                            $('#transferColumn').modal('hide');
                            _this.$toastr.s("Column Successfully Transferred");
                        } else {
                            $('#transferColumn').modal('hide');
                        }
                    })
                    .catch(error => {

                    });
            },
            RemoveNodeAndChildren() {
                var _this = this;
                var postData = {
                    id: this.selectedData.cardId,
                    text: this.selectedData.data
                };
                Swal.fire({
                    title: 'You want to delete this task?',
                    text: "",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'

                }).then((result) => {
                    if (result.value) {
                        axios.post('/api/task-list/delete-task', postData)
                            .then(response => response.data)
                            .then(response => {
                                _this.getBoardTask();
                                _this.boardSocketCall();
                                _this.$toastr.w("Card Successfully Deleted !");
                                setTimeout(() => {
                                    Swal.close();
                                }, 1000);
                            })
                            .catch(error => {
                                console.log('Api for delete task not Working !!!')
                            });
                    }
                })

            },
            getBoardTask() {
                var _this = this;
                var datePicker = new Date();
                datePicker.setDate(datePicker.getDate() - 1);
                _this.disabledDates = {
                    to: datePicker, // Disable all dates up to specific date
                };
                var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                let data = {
                    projectId: this.projectId,
                    board_id: this.board_id,
                    nav_id: this.nav_id,
                    tz :tz
                };
                axios.post('/api/board-task', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.list.name = response.board.board_title;
                        _this.list.description = response.board.description;
                        if (response.checkEmptyColumn === 0){
                            Swal.fire({
                                title: "Your Filter Has 0 Results",
                                type: "warning",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: "Reset Filter"
                            }).then((result) => {
                                if (result.value) {
                                    _this.getBoardTaskFilter('all');
                                    setTimeout(() => {
                                        Swal.close();
                                    }, 1000);
                                }
                            })
                        }else {
                            _this.allCardIds = response.allCardIds;
                            _this.cards = response.success;
                            _this.allUsers = response.allUsers;
                            _this.allTags = response.allTags;
                            _this.getData();
                            _this.GetAllBoardCard(data);
                            Bus.$emit('AddedFilterSuccess');

                        }
                    })
                    .catch(error => {
                        console.log('Api not working..')
                    });
            },
            GetAllBoardCard(data){
                let _this = this;
                axios.post('/api/all-board-card', data)
                    .then(response => response.data)
                    .then(response => {
                            _this.allCardIds = response.allCardIds;
                            _this.cards = response.success;
                            _this.getData();
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            getBoardTaskFilter(type) {
                var _this = this;
                var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                let data = {
                    projectId: this.projectId,
                    board_id: this.board_id,
                    nav_id: this.nav_id,
                    type: type,
                    users: [],
                    filter: this.selectedPriorites,
                    tz: tz
                };
                if (this.userIdList.length > 0) {
                    data.users = this.userIdList;
                }
                axios.post('/api/board-task-filter', data)
                    .then(response => response.data)
                    .then(response => {
                        if (response.checkEmptyColumn === 0) {
                            Swal.fire({
                                title: "Your Filter Has 0 Results",
                                type: "warning",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Reset Filter'

                            }).then((result) => {
                                if (result.value) {
                                    _this.getBoardTaskFilter('all');
                                    setTimeout(() => {
                                        Swal.close();
                                    }, 1000);
                                }
                            })
                        } else {
                            _this.allCardIds = response.allCardIds;
                            _this.cards = response.success;
                            _this.allUsers = response.allUsers;
                            _this.allTags = response.allTags;
                            _this.getData();
                            Bus.$emit('AddedFilterSuccess');
                        }
                    })
                    .catch(error => {
                    });
                this.filter_types = null;
            },
            clearInputFeild() {
                $("#EditModal").modal('hide');
                $("#addModal").modal('hide');
                $("#addExistingTask").modal('hide');
                this.addField = {};
            },
            AddExistingTasks() {
                let _this = this;
                let total = this.selectedExistedTask.length;
                if (total <= 0) {
                    Swal.fire('Warning!', 'No Task To Add', 'warning');
                    setTimeout(() => {
                        Swal.close();
                    }, 1000);
                    return false;
                }
                let data = {
                    'multiple_board_id': this.selectedSubBoard,
                    'tasks': this.selectedExistedTask,
                    'id': this.currentColumn
                };
                axios.post('/api/add-existing-tasks', data)
                    .then(response => response.data)
                    .then(response => {
                        for (var i = 0; i < response.data.length; i++) {
                            _this.cards[_this.currentColumnIndex].task.push({
                                id: response.data[i].id,
                                name: response.data[i].title,
                                date: response.data[i].date,
                                tags: response.data[i].tag,
                                types: 'task',
                                clicked: 0
                            });
                        }
                        _this.getBoardTask();
                        _this.boardSocketCall();
                    })
                    .catch(error => {
                    });

                this.updateIndex = null;
                this.selectedExistedTask = [];
                $("#addExistingTask").modal('hide');
            },
            makeInput(e, id) {
                let _this = this;
                $('#title' + id).removeClass('card-title-blur');
                $('#title' + id).addClass('card-title-focus');
                $('.dropdowns-card-user').hide();
            },
            addCard(index, id) {
                let _this = this;
                axios.post('/api/card-add', {'id': id})
                    .then(response => response.data)
                    .then(response => {
                        if (response.success === true) {
                            let data = response.data;
                            _this.cards[index].task.unshift({
                                id: data.id,
                                name: data.title,
                                date: data.date,
                                tags: [],
                                assigned_user: [],
                                users: [],
                                clicked: 0,
                                textareaShow: true
                            });
                            let keys = _this.cards[index].task.length - 1;
                            _this.getBoardTask();
                            _this.$toastr.s("Card Successfully added !");
                            setTimeout(function () {
                                $('#title' + data.id).click();
                                $('#title' + data.id).focus();
                            }, 1000)
                        }
                        _this.boardSocketCall();
                    })
                    .catch(error => {
                    });
            },
            deleteCard(index, cardIndex, id) {
                let _this = this;
                Swal.fire({
                    title: 'Are you sure you want to delete this card?',
                    text: "",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'

                }).then((result) => {
                    if (result.value) {
                        if (_this.cards[index].task[cardIndex].id === id) {
                            axios.get('/api/card-delete/' + id)
                                .then(response => response.data)
                                .then(response => {
                                    if (response.success === true) {
                                        let keys = _this.cards[index].task.length - 1;
                                        _this.getBoardTask();
                                        _this.getData();
                                        _this.boardSocketCall();
                                        setTimeout(function () {
                                            $('#id' + index + keys).click();
                                            $('#id' + index + keys).focus();
                                        }, 100);
                                        _this.$toastr.s("Card Successfully deleted !");
                                        setTimeout(() => {
                                            Swal.close();
                                        }, 1000);
                                    }
                                })
                                .catch(error => {
                                });
                        }
                    }
                })

            },
            deleteTask(index, cardIndex, id) {
                let _this = this;
                Swal.fire({
                    title: 'Are you sure',
                    text: "You want to remove this task?",
                    type: 'warning',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "Yes, remove it"
                }).then((result) => {
                    if (result.value) {
                        if (_this.cards[index].task[cardIndex].id === id) {
                            axios.get('/api/board-task-delete/' + id)
                                .then(response => response.data)
                                .then(response => {
                                    _this.cards[index].task.splice(cardIndex, 1);
                                    _this.getData();
                                    _this.boardSocketCall();
                                    _this.$toastr.w("Card Successfully removed !");
                                    setTimeout(() => {
                                        Swal.close();
                                    }, 1000);
                                })
                                .catch(error => {
                                });
                        } else {
                        }
                    }
                })

            },
            deleteAllTasks(index, cardIndex, id) {
                $('.jquery-accordion-menu').hide();
                let _this = this;
                id = {
                    'id': this.selectedIds
                };
                Swal.fire({
                    title: 'Are you sure',
                    text: "You want to remove this task?",
                    type: 'warning',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "Yes, remove it"
                }).then((result) => {
                    if (result.value) {
                        axios.post('/api/board-task-delete/', id)
                            .then(response => response.data)
                            .then(response => {
                                _this.cards[index].task.splice(cardIndex, 1);
                                _this.getBoardTask();
                                _this.boardSocketCall();
                                _this.selectedIds = [];
                                $('.card-list').removeClass('selected-card');
                                _this.$toastr.w("Card Successfully removed !");
                                setTimeout(() => {
                                    Swal.close();
                                }, 1000);
                            })
                            .catch(error => {

                            });
                    }
                })

            },
            addTag(e, index, key) {
                if (e.which === 13) {
                    this.cards[index].task[key].tags.splice(0, 1, this.tag);
                    this.tag = null;
                }
            },
            addExistingTag(index, tagIndx, key, card, dntfrgt = '') {
                let _this = this;
                if (dntfrgt !== '') {
                    var postData = {
                        id: card.cardId,
                        tags: "Dont Forget",
                        color: "#FF0000",
                        type: 'task',
                    };
                } else {
                    var postData = {
                        id: card.cardId,
                        tags: this.cards[index].task[key].existing_tags[tagIndx].title,
                        color: this.cards[index].task[key].existing_tags[tagIndx].color,
                        type: 'task',
                    };
                }
                axios.post('/api/task-list/add-tag', postData)
                    .then(response => response.data)
                    .then(response => {
                        _this.$toastr.s("Tag Successfully Added !");
                        setTimeout(function () {
                            _this.getBoardTask();
                            _this.listSocketCall(card.list_id);
                            _this.searchTag = null;
                            _this.filterTag = 0;
                        }, 100);
                    })
                    .catch(error => {
                        console.log("1st error =>" + error)
                    });
            },

            addExistingTagSearch(index, tagIndx, key, card, allTags, dntfrgt = '') {
                let _this = this;
                if (dntfrgt !== '') {
                    var postData = {
                        id: card.cardId,
                        tags: "Dont Forget",
                        color: "#FF0000",
                        type: 'task',
                    };
                } else {
                    var postData = {
                        id: card.cardId,
                        tags: allTags[tagIndx].title,
                        color: allTags[tagIndx].color,
                        type: 'task',
                    };
                }

                axios.post('/api/task-list/add-tag', postData)
                    .then(response => response.data)
                    .then(response => {
                        _this.$toastr.s("Tag Successfully Added !");
                        setTimeout(function () {
                            _this.getBoardTask();
                            _this.listSocketCall(card.list_id);
                            _this.searchTag = null;
                            _this.filterTag = 0;
                        }, 100);
                    })
                    .catch(error => {
                        console.log("1st error =>" + error)
                    });
            },
            showDescription(data) {
                let _this = this;
                _this.selectedData = data;
                this.selectedData.description = data.description;
                this.selectedData.list_id = data.list_id;

                setTimeout(function () {
                    _this.onBlurDetails();
                    $('#description-model').modal('show');
                }, 80)
            },
            updateDescription() {
                var _this = this;
                var postData = {
                    id: _this.selectedData.cardId,
                    details: _this.selectedData.description
                };
                axios.post('/api/task-list/update', postData)
                    .then(response => response.data)
                    .then(response => {
                        _this.getBoardTask();
                        _this.$toastr.i("Task Description update");
                        if (response.users.length > 0) {
                            _this.Socket.emit('notification-update', response.users)
                        }
                        _this.Socket.emit('taskUpdate', {
                            project_id: _this.projectId,
                            list_id: _this.list.id,
                            board_id: _this.selectedData.multiple_board_id,
                            user_id: _this.authUser.id,
                            task_id: _this.selectedData.id
                        });
                    })
                    .catch(error => {
                        console.log('Api for move down task not Working !!!')
                    });
            },
            onBlurDetails() {
                let _this = this;
                _this.editorsDesc.setData(_this.selectedData.description);
                _this.editorsDesc.on('blur', function (evt) {
                    let datas = evt.editor.getData();
                    if (_this.selectedData.description !== datas) {
                        _this.selectedData.description = datas;
                        _this.updateDescription('#bx' + _this.selectedData.cardId, datas);
                    }
                });
                $('#cke_1_contents').on('paste', function (event) {
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

            deleteColumn(index, id) {
                let _this = this;
                Swal.fire({
                    title: "Are you sure?",
                    text: "",
                    type: "warning",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.value) {
                        axios.get('/api/board-delete/' + id)
                            .then(response => response.data)
                            .then(response => {
                                if (response.success) {
                                    _this.cards.splice(index, 1);
                                    _this.getData();
                                    _this.boardSocketCall();
                                    _this.$toastr.s("Column Successfully Deleted !");
                                    setTimeout(() => {
                                        Swal.close();
                                    }, 1000);
                                }
                            })
                            .catch(error => {
                            });
                    }
                })

            },
            hideItem(index) {

            },
            hideColumn(index, id) {
                let _this = this;
                let ishide = {
                    "hide": 1
                };
                axios.post('/api/board-hide/' + id, ishide)
                    .then(response => response.data)
                    .then(response => {
                        _this.cards[index].hidden = 1;
                        _this.getData();
                        _this.boardSocketCall();
                    })
                    .catch(error => {
                    });
            },
            showColumn(index, id) {
                let _this = this;
                let ishide = {
                    "hide": 0
                };
                axios.post('/api/board-hide/' + id, ishide)
                    .then(response => response.data)
                    .then(response => {
                        _this.cards[index].hidden = 0;
                        _this.getData();
                        _this.boardSocketCall();
                    })
                    .catch(error => {
                    });
            },
            preventEnter(e) {
                if (e.which === 13) {
                    e.preventDefault();
                }
            },
            cardTitlePress(e, card, index, key) {
                $('.dropdowns-card-user').hide();
                let _this = this;
                let title = $('#title' + card.cardId).text();
                if (e.which === 13) {
                    card.data = title;
                    _this.saveData(card, index, key);
                    _this.addCard(0, _this.selectedColumn);
                }
                if (e.which === 32 || e.which === 13) {
                    _this.triggers = false;
                    _this.userNames = '';
                    _this.projectUsers = null;
                    $('.dropdowns-card-user').hide();
                }
                if (_this.triggers === true && e.which !== 16 && e.which !== 50) {
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
                                _this.projectUsers = response.search_user;
                                if (_this.projectUsers.length > 0) {
                                    $('#titleUserMention' + card.cardId).show();
                                }
                            })
                            .catch(error => {
                                console.log('search user is not Working !!!')
                            });
                    }
                }

                if (e.shiftKey && e.which === 50) {
                    _this.allTags = null;
                    _this.triggers = true;
                    _this.commentsData = $('#title' + card.cardId).text();
                    axios.get('/api/task-list/all-suggest-user')
                        .then(response => response.data)
                        .then(response => {
                            $('.dropdowns-card-user').hide();
                        })
                        .catch(error => {
                            console.log('All suggest user api not working')
                        })
                }
                if (e.shiftKey && e.which === 51) {
                    _this.projectUsers = null;
                    _this.tagTriggers = true;
                    _this.commentsData = $('#title' + card.cardId).text();
                    axios.get('/api/task-list/all-tag-for-manage')
                        .then(response => response.data)
                        .then(response => {
                            _this.allTags = response.tags;
                            $('.dropdowns-card-user').hide();
                            $('#titleUserMention' + card.cardId).show();
                        })
                        .catch(error => {
                            console.log('All suggest user api not working')
                        })
                }
            },
            SearchTaskByAssignedUsers(id, name, card, user) {
                let _this = this;
                $('#title' + card.cardId).focus();
                _this.assignUserToTask(user, 0, 0, card);
                $('#title' + card.cardId).html(_this.commentsData + '' + name + ' ');
                _this.projectUsers = null;
                $('.dropdowns-card-user').hide();
            },
            tagMention(card, tag, index, tagIndx, key) {
                let _this = this;
                $('#title' + card.cardId).focus();
                $('#title' + card.cardId).html(_this.commentsData + '' + tag.title + ' ');
                _this.addExistingTag(index, tagIndx, key, card, '');
                _this.allTags = null;
                $('.dropdowns-card-user').hide();
            },
            showItem(e, data, index, child_key) {

                $('#title' + data.cardId).addClass('card-title-blur').removeClass('card-title-focus');
                setTimeout(() => {
                    $('.dropdowns-card-user').hide();
                }, 300);
                let attDataNew = $('#title' + data.cardId).text();
                data.data = attDataNew;
                this.saveData(data, index, child_key);
            },
            showHideTextarea(id) {
                let _this = this;

                $('.inp').addClass('input-hide').removeClass('form-control');
                $(id).removeClass('input-hide').addClass('form-control');
                setTimeout(() => {
                    $(id).click();
                    $(id).focus();
                }, 100);
                var option = {
                    height: 50,
                    maxHeight: 200
                };
                _this.growInit(option);
            },
            saveData(data, index, child_key) {
                let _this = this;
                if (data.data === "") {
                    axios.get('/api/card-delete/' + data.cardId)
                        .then(response => response.data)
                        .then(response => {
                            if (response.success === true) {
                                let keys = _this.cards[index].task.length - 1;
                                _this.getBoardTask();
                                _this.getData();
                                _this.boardSocketCall();
                            }
                        })
                        .catch(error => {
                        });


                } else {
                    let title = {
                        'title': data.data
                    };
                    axios.post('/api/card-update/' + data.cardId, title)
                        .then(response => response.data)
                        .then(response => {
                            _this.cards[index].task[child_key].name = data.data;
                            _this.getData();
                            _this.listSocketCall(data.list_id);
                            _this.Socket.emit('notification-update', response.users)
                        })
                        .catch(error => {
                        });
                }
            },

            saveCardData(e, data) {
                if (e.which === 13) {
                    $('.inp').addClass('input-hide').removeClass('form-control');
                }
            },
            updateDate(date) {
                var _this = this;
                date = new Date(date);
                var month = (parseFloat(date.getMonth() + 1) > 9) ? parseFloat(date.getMonth() + 1) : '0' + parseFloat(date.getMonth() + 1);
                var formatedDate = date.getFullYear() + '-' + month + '-' + date.getDate() + ' 23:59:00';

                var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                setTimeout(function () {
                    let data = {
                        'date': formatedDate,
                        'tz': tz,
                    };
                    axios.post('/api/card-update/' + _this.selectedCard, data)
                        .then(response => response.data)
                        .then(response => {
                            _this.listSocketCall(card.list_id);
                            _this.Socket.emit('notification-update', response.users);
                            _this.$toastr.e("Date Successfully updated!");
                        })
                        .catch(error => {
                        });
                }, 300)
            },
            changeTag(tags, card, columnIndex, cardIndex) {
                var _this = this;
                var old = this.cards[columnIndex].task[cardIndex].tags.length;
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
                            _this.$toastr.s("Tag Successfully Changed!");
                            setTimeout(function () {
                                _this.getBoardTask();
                                _this.listSocketCall(card.list_id);
                                $('.dropdown-menu').removeClass('show');
                            }, 100);
                        })
                        .catch(error => {
                            console.log("2nd error =>" + error)
                        });
                }
            },
            deleteCardTag(obj, card, columnIndex, cardIndex) {
                var _this = this;
                var postData = {
                    assign_id: obj.tag.assign_id,
                };
                if (obj.tag.text !== 'Dont Forget') {
                    axios.post('/api/task-list/delete-tag', postData)
                        .then(response => response.data)
                        .then(response => {
                            _this.cards[columnIndex].task[cardIndex].tags.splice(obj.index, 1);
                            setTimeout(function () {
                                _this.getBoardTask();
                                _this.listSocketCall(card.list_id);
                                _this.$toastr.w("Tag Successfully deleted!");
                            }, 100);
                            _this.tags = [];
                        })
                        .catch(error => {
                            console.log('Api for delete tag not Working !!!')
                        });
                }

            },
            getAllTask() {
                this.tree4data = [];
                let data = {
                    id: this.projectId,
                    nav_id: this.selectedNav,
                    list_id: this.selectedSubNav,
                };
                axios.post('/api/all-task-list', data)
                    .then(response => response.data)
                    .then(response => {
                        this.tree4data = response.task_list;
                    })
                    .catch(error => {

                    });
            },
            showLog(card) {
                var _this = this;
                $('#title' + card.cardId).blur();

                setTimeout(function () {
                    _this.ShowDetails();
                    $('#_file').click();
                    $('#_details').click();
                }, 300)

            },
            selectCard(card, child) {
                this.selectedData = card;
                this.selectedCard = card.cardId;
                this.task_logs = null;
                this.HideDetails();
                $('.card-list').css("background-color", "#ffffff");
                $('#card_' + this.selectedCard).css("background-color", "#ddf3fd");
            },
            ShowDetails() {
                var _this = this;
                if (_this.selectedData != null && _this.selectedData.sort_id !== -2) {
                    $('#task_width').addClass('task_widthNormal');
                    $('#details').addClass('details-show');
                    $('.jquery-accordion-menu').hide();
                    $('#task_width').removeClass('task_width');
                }
            },
            Add_Priority(priority, card) {
                var _this = this;
                var data = {
                    ids: (card === null) ? _this.selectedIds : [card.cardId],
                    priority: priority
                };
                axios.post('/api/task-list/add-priority', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.getBoardTask();
                        (card === null) ? _this.boardSocketCall() : _this.listSocketCall(card.list_id);
                        _this.selectedIds = [];
                        $('.card-list').removeClass('selected-card');
                        $('.jquery-accordion-menu').hide();
                        _this.$toastr.s("Priority Successfully Added!");
                        Bus.$emit('UpdateListOrBoard');
                        _this.Socket.emit('taskUpdate', {
                            project_id: _this.projectId,
                            list_id: _this.list.id,
                            board_id: _this.selectedData.multiple_board_id,
                            user_id: _this.authUser.id,
                            task_id: _this.selectedData.id
                        })
                    })
                    .catch(error => {
                        console.log('Api for task add priority not Working !!!')
                    });
            },
            RemovePriority(card) {
                var _this = this;
                var data = {
                    ids: (card === null) ? _this.selectedIds : [card.cardId],
                    priority: null
                };
                axios.post('/api/task-list/add-priority', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.getBoardTask();
                        (card === null) ? _this.boardSocketCall() : _this.listSocketCall(card.list_id);
                        _this.selectedIds = [];
                        $('.card-list').removeClass('selected-card');
                        $('.jquery-accordion-menu').hide();
                        _this.$toastr.w("Priority Successfully Removed!");
                    })
                    .catch(error => {
                        console.log('Api for task add priority not Working !!!')
                    });
            },
            HideDetails() {
                $('#task_width').addClass('task_width');
                $('#task_width').removeClass('task_widthNormal');
                $('#details').removeClass('details-show');
            },
            ShowTextArea(data) {
                var _this = this;
                $('.SubmitButton').show();
                var option = {
                    height: 50,
                    maxHeight: 200
                };
                _this.growInit(option);
            },
            showAssignedUserRemoveButton(data) {

                $('[data-toggle="tooltip"]').tooltip('hide');

                setTimeout(function () {
                    $('#remove-assign-user' + data.cardId).toggleClass('remove-assign-user');
                    $('#remove-assign-user' + data.cardId).removeClass('remove-assigned');
                }, 500)

            },
            HideCustomMenu() {
                $('.jquery-accordion-menu').hide();
            },
            makeItClick(e, card, child, index, key, boardId) {
                this.currentColumn = boardId;
                this.currentColumnIndex = index;
                this.currentColumnKey = key;
                this.selectedCards = card;
                var _this = this;
                if (e.ctrlKey && e.which === 1) {
                    if (_this.selectedIds.includes(card.cardId)) {
                        var indexs = _this.selectedIds.indexOf(card.cardId);
                        if (indexs > -1) {
                            $('#card_' + _this.selectedIds[indexs]).removeClass('selected-card');
                            _this.selectedIds.splice(indexs, 1);
                        }
                    } else {
                        _this.selectedIds.push(card.cardId);
                    }
                    for (let index = 0; index < _this.selectedIds.length; index++) {
                        $('#card_' + _this.selectedIds[index]).addClass('selected-card');
                    }
                } else if (e.shiftKey && e.which === 1) {
                    var first = _this.shift_first;
                    var last = card.cardId;
                    var flag = 0;
                    var flag1 = 0;
                    var index_last = _this.allCardIds.indexOf(last);
                    var index_first = _this.allCardIds.indexOf(first);
                    if (index_first > index_last) {
                        first = card.cardId;
                        index_first = _this.allCardIds.indexOf(first);
                        last = _this.shift_first;
                    }
                    _this.selectedIds = [];
                    $('.card-list').removeClass('selected-card');
                    for (var i = index_first; i <= _this.allCardIds.length; i++) {
                        if (_this.allCardIds[i] === first) {
                            flag = 1;
                            flag1 = 1;
                        }
                        if (flag === 1) {
                            _this.selectedIds.push(_this.allCardIds[i]);
                            $('#card_' + _this.allCardIds[i]).addClass('selected-card');
                        }
                        if (flag1 === 1 && _this.allCardIds[i] === last) {
                            flag = 0;
                            flag1 = 0;
                            break;
                        }
                    }
                } else if (e.which === 1) {
                    _this.selectedIds = [];
                    _this.selectedIds.push(card.cardId);
                    _this.shift_first = card.cardId;

                    this.selectedData = card;
                    this.selectedCard = card.cardId;
                    this.task_logs = null;
                    this.HideDetails();
                    $('.card-list').removeClass('selected-card');
                    $('#card_' + this.selectedCard).addClass('selected-card');

                } else if (e.which === 3) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (_this.context_menu_flag !== 1) {
                        $('#rmenu').addClass('menu-show');
                        let target = $(e.target);
                        let w = target.closest('#board_view_list').width();
                        let h = target.closest('#board_view_list').height();
                        let p = target.closest('#board_view_list').offset();
                        let lScroll = $('#col10').scrollLeft();
                        let left = e.clientX - p.left + lScroll;
                        let top = e.clientY - p.top;
                        let clickH = $('.jquery-accordion-menu').height();
                        clickH = clickH < 150 ? 400 : clickH;
                        /*if ((w - left) < 230) {
                            left = w - 250;
                        }*/
                        if (h < top + clickH) {
                            top = top - (top + clickH - h);
                        }
                        if (top < 10) {
                            top = 10;
                        }

                        let ttarget = target.closest('#board_view_list').find('.jquery-accordion-menu');

                        if (_this.selectedIds.length > 0) {
                            var index = _this.selectedIds.indexOf(card.cardId);
                            if (index > -1) {
                                ttarget.css({
                                    top: top,
                                    left: left,
                                }).fadeIn();
                            } else {
                                $('.eachItemRow').removeClass('clicked');

                                $('.jquery-accordion-menu').hide();
                                _this.selectedIds = [];
                                $('.card-list').removeClass('selected-card');
                            }
                        }
                    }
                } else if (e.ctrlKey && e.which === 65) {
                }
            },
            removeAssignedUser(user, card) {
                var _this = this;
                var postData = {
                    user_id: user.id,
                    task_id: card.cardId
                };
                axios.post('/api/task-list/assign-user-remove', postData)
                    .then(response => response.data)
                    .then(response => {
                        if (response === 'success') {
                            _this.getBoardTask();
                            _this.listSocketCall(card.list_id);
                            _this.Socket.emit('notification-update', response.users);
                            _this.$toastr.w("Assign-User Successfully Removed!");
                        }
                    })
                    .catch(error => {
                        console.log('Api assign-user-remove is not Working !!!')
                    });
            },
            assignUserToTask(user, index, key, data) {
                var _this = this;
                var postData = {
                    task_id: data.cardId,
                    user_id: user.id
                };
                axios.post('/api/task-list/assign-user', postData)
                    .then(response => response.data)
                    .then(response => {
                        if (response.success === 'success') {
                            _this.$toastr.s("Successfully Assign user to task !");
                            setTimeout(function () {
                                _this.getBoardTask();
                                _this.listSocketCall(data.list_id);
                                _this.Socket.emit('notification-update', response.users);
                            }, 100);
                        }
                    })
                    .catch(error => {
                        _this.$toastr.e("Api is not Working !!!");
                    });
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

                    });
            },
            updateTagColor(e, tag) {
                var color = e.target.value;
                var _this = this;
                var postData = {
                    id: tag.id,
                    color: color,
                };
                axios.post('/api/task-list/update-tag', postData)
                    .then(response => response.data)
                    .then(response => {
                        _this.manageTag = response.tags;
                        _this.getBoardTask();

                        _this.$toastr.s("Tag Successfully Updated!");
                    })
                    .catch(error => {
                        console.log('Api for update color of tag not Working !!!')
                    });

            },
            DeleteTagFromModal(tag) {
                var _this = this;
                Swal.fire({
                    title: 'Are you sure you want to delete the tag?',
                    text: "",
                    type: 'warning',
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#3085d6',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                        var postData = {
                            id: tag.id,
                            title: tag.title,
                        };
                        axios.post('/api/task-list/delete-tag', postData)
                            .then(response => response.data)
                            .then(response => {
                                _this.manageTag = response.tags;
                                _this.getBoardTask();
                                _this.$toastr.w("Tag Successfully Deleted!");
                                setTimeout(() => {
                                    Swal.close();
                                }, 1000);
                            })
                            .catch(error => {
                                console.log('Api for delete tag not Working !!!');
                            });
                    }
                })

            },
            ruleAlertHeight: function (obj, trigger) {
                setTimeout(function () {
                    let target = $('#' + obj);
                    let h = 0;
                    if (target.length > 0) {
                        h = $('#' + obj).height();
                    }
                    $('#' + trigger).css({top: h + 'px'});
                }, 400)
            },
            updateTagName(e, tag) {
                var newTag = e.target.innerText;
                if (e.which === 13) {
                    var _this = this;
                    var postData = {
                        id: tag.id,
                        tag: newTag,
                    };
                    axios.post('/api/task-list/update-tag', postData)
                        .then(response => response.data)
                        .then(response => {
                            _this.manageTag = response.tags;
                            _this.getBoardTask();
                            _this.$toastr.w("Tag Successfully Update!");
                        })
                        .catch(error => {
                            console.log('Api for update tag not Working !!!')
                        });
                }
            },
            newLineoff(e) {
                if (e.which === 13) {
                    e.preventDefault();
                }
            },
            switchEvent(e) {
                $(e.target).closest('.eachItemRow').find('.switchToggle').collapse('toggle');
            },
            ActionToSelectedTask(value, type) {
                var _this = this;
                setTimeout(function () {
                    if (type === 'date') {
                        var date = new Date(_this.date_for_selected);
                        var month = (parseFloat(date.getMonth() + 1) > 9) ? parseFloat(date.getMonth() + 1) : '0' + parseFloat(date.getMonth() + 1);
                        var day = (parseFloat(date.getDate() + 1) > 9) ? parseFloat(date.getDate()) : '0' + parseFloat(date.getDate());
                        var date_for_selected = date.getFullYear() + '-' + month + '-' + day;
                    }
                    var postData = {
                        ids: _this.selectedIds,
                        type: type,
                        value: type === 'date' ? date_for_selected : value,
                    };

                    axios.post('/api/task-list/assign-user-add-tag', postData)
                        .then(response => response.data)
                        .then(response => {
                            _this.getBoardTask();
                            $('.jquery-accordion-menu').hide();
                            _this.selectedIds = [];
                            _this.searchTag = null;
                            _this.filterTag = 0;
                            $('.card-list').removeClass('selected-card');
                        })
                        .catch(error => {
                            console.log('Api for delete task not Working !!!')
                        });
                }, 500)
            },
            openPicker: function () {
                let _this = this;
                setTimeout(function () {
                    let target = $('.vdp-datepicker__calendar:visible');
                    let wH = window.innerHeight + 140;
                    let position = target.offset();
                    let tH = target.height();
                    let cH = wH - position.top;
                    if (cH < tH) {
                        target.css({bottom: 0 + 'px'});
                    }
                }, 200)
            },
            deleteSelectedTask() {
                var _this = this;
                _this.delete_popup = 1;
                var postData = {
                    ids: _this.selectedIds,
                };
                $('.jquery-accordion-menu').hide();
                Swal.fire({
                    title: "Are you sure",
                    text: "You want to delete all selected tasks?",
                    type: "warning",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "Yes, delete them",

                }).then((result) => {
                    if (result.value) {
                        axios.post('/api/task-list/delete-task', postData)
                            .then(response => response.data)
                            .then(response => {
                                _this.getBoardTask();
                                _this.boardSocketCall();
                                $('.jquery-accordion-menu').hide();
                                _this.delete_popup = 0;
                                _this.$toastr.w("Selected Task Successfully Deleted");
                                setTimeout(() => {
                                    Swal.close();
                                }, 1000);
                            })
                            .catch(error => {
                                console.log('Api for delete task not Working !!!')
                            });
                    }
                })

            },
            addUserToFilter(userId) {
                if (this.userIdList.includes(userId)) {
                    var indexs = this.userIdList.indexOf(userId);
                    if (indexs > -1) {
                        this.userIdList.splice(indexs, 1);
                    }
                } else {
                    this.userIdList.push(userId);
                }
            },
            userFilter() {
                if (this.userIdList.length < 1) {
                    Swal.fire('Warning!!', "No user is selected ", "warning");
                } else {
                    this.getBoardTaskFilter(this.filter_types);
                    $('#user_list').modal('hide');
                }

            },
            checkedAllUser(allUsers) {
                if ($('.checkedAllUser').prop('checked') === false) {
                    this.userIdList = [];
                } else {
                    for (let i = 0; allUsers.length > i; i++) {
                        this.userIdList.push(allUsers[i].id);
                    }
                }
            },
            priorityHide() {
                if (this.selectedPriorites.length <= 0) {
                    Swal.fire('Warning!!', 'Nothing selected', 'warning');
                    return false;
                }
                this.getBoardTaskFilter('p_hide');
                $('#priority_list_modal').modal('hide');
            },
            priorityShow() {
                if (this.selectedPriorites.length <= 0) {
                    Swal.fire('Warning!!', 'Nothing selected', 'warning');
                    return false;
                }
                this.getBoardTaskFilter('p_show');
                $('#priority_list_modal').modal('hide');
            },
            addFilterToFilter(type) {
                if (this.selectedPriorites.includes(type)) {
                    var indexs = this.selectedPriorites.indexOf(type);
                    if (indexs > -1) {
                        this.selectedPriorites.splice(indexs, 1);
                    }
                } else {
                    this.selectedPriorites.push(type);
                }
            },
            boardSocketCall(list_id = null) {
                let _this = this;
                _this.Socket.emit('cardUpdated', {
                    project_id: _this.projectId,
                    board_id: _this.board_id,
                    list_id: list_id,
                    user_id: _this.authUser.id,
                });
            },
            listSocketCall(list_id = null) {
                let _this = this;
                _this.Socket.emit('taskUpdate', {
                    project_id: _this.projectId,
                    board_id: _this.board_id,
                    list_id: list_id,
                    user_id: _this.authUser.id,
                    task_id: _this.selectedData.id
                });
            },
            sendMail(data) {
            },
            allTeamUsers() {
                let _this = this;
                let data = {
                    project_id: _this.projectId,
                };

                axios.post('/api/all-project-users', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.allTeamsUsers = response.users;
                    })
                    .catch(error => {
                        console.log('Project users api not working')
                    });
            },
            suggestUsers(index, key) {
                let _this = this;
                let data = {
                    project_id: _this.projectId,
                    name: _this.userSeggistion
                };

                axios.post('/api/project-users', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.allTeamsUsers = response.users;
                    })
                    .catch(error => {
                    });
            },

            searchTags(index, key, type) {
                let _this = this;
                let data = {
                    title: _this.searchTag
                };
                axios.post('/api/search-tag', data)
                    .then(response => response.data)
                    .then(response => {
                        if(type === 'single'){
                            _this.searchAllTags = response.allTags;
                        }else{
                            _this.allTags = response.allTags;
                        }
                    })
                    .catch(error => {
                        console.log('Api not working..')
                    });
            },
            selectColumn(id) {
                let _this = this;
                _this.selectedColumn = id;
                $('.card-container').removeClass('columnSelected');
                if (_this.selectedIds.length > 0) {
                    $('.card-container-' + id).addClass('columnSelected');
                }
            },
            pastCopyAndCut() {
                let _this = this;
                var data = _this.selectedColumn;
                var postData = {
                    target_id: _this.selectedColumn,
                    copy_ids: _this.copySelectedId,
                    type: _this.copyOrCut,
                    nav_id: _this.nav_id
                };
                if (_this.copyOrCut !== null) {
                    axios.post('/api/board-card/copy-cut-past', postData)
                        .then(response => response.data)
                        .then(response => {
                            _this.getBoardTask();
                            setTimeout(() => {
                                _this.$toastr.s("Paste successfull");
                            }, 500);
                            _this.copySelectedId = [];
                            _this.copyOrCut = null;
                        })
                        .catch(error => {
                            console.log('Api is copy and cut not Working !!!')
                        });
                }

            },
            shwAssignUserDropDown() {
                let _this = this;
                let target = null;
                for (let index = 0; index < _this.selectedIds.length; index++) {
                    target = $('#card_' + _this.selectedIds[index]).find('a.user-assign-card > span.dropdown-toggle-split');
                    if (target.length > 0) {
                        $(target[0]).click();
                    } else {
                        target = $('#card_' + _this.selectedIds[index]).find('a.user-assign-card > span.assigned_user-card');
                        if (target.length > 0) {
                            $(target[0]).click();
                        }
                    }
                }
            },
            showtagBox() {
                let _this = this;
                let target = null;
                for (let index = 0; index < _this.selectedIds.length; index++) {
                    target = $('#card_' + _this.selectedIds[index]).find('a.tag-icon > i.icon-image-preview');

                    if (target.length > 0) {
                        $(target[0]).click();
                    } else {
                        target = $('#card_' + _this.selectedIds[index]).find('a.tag-icon > div > div > div.dropdown-toggle-split');
                        if (target.length > 0) {
                            $(target[0]).click();
                        }
                    }
                }
            },
            moveCard(direction) {
                let _this = this;
                let selectedindexs = _this.allCardIds.indexOf(_this.selectedIds[0]);
                if (direction === 'down'){
                    var downData = _this.allCardIds[selectedindexs];
                    var upData = _this.allCardIds[selectedindexs + 1];
                }else if (direction === 'up'){
                    var downData = _this.allCardIds[selectedindexs];
                    var upData = _this.allCardIds[selectedindexs - 1];
                }

                let postData = {
                    column: _this.selectedColumn,
                    ids: {downData, upData},
                };

                axios.post('/api/board-card/move-up-down', postData)
                    .then(response => response.data)
                    .then(response => {
                        _this.getBoardTask();
                        _this.selectedIds = [];
                        $('.card-list').removeClass('selected-card');
                    })
                    .catch(error => {
                        console.log('Api is move up not Working !!!')
                    });
            },
            GetFilterData(val) {
                this.filter_types = val;
                if (val === 'my') {
                    this.userIdList = [];
                    this.getBoardTaskFilter(val);
                } else if (val === 'users_task') {
                    $('#user_list').modal('show');
                } else if (val === 'all' || val === 'not_assign' || val === 'priority' || val === 'asc' || val === 'desc' || val === 'date-asc' || val === 'date') {
                    this.getBoardTaskFilter(val);
                } else if (val === 'priority_based') {
                    $('#priority_list_modal').modal('show');
                }
            },
            UpdateListOrBoard() {
                var _this = this;
                var l = Ladda.create(document.querySelector('.ladda_update_list_board'));
                l.start();
                this.list.project_id = this.projectId;
                this.list.nav_id = this.nav_id;
                this.list.id = _this.board_id;
                axios.post('/api/board-list-update', this.list)
                    .then(response => response.data)
                    .then(response => {
                        if (response.status === 500){
                            _this.boardListErrors = response.error;
                        }else {
                            l.stop();
                            localStorage.selected_nav = JSON.stringify({
                                title: _this.list.name,
                                description: _this.list.description,
                                list_id: _this.list.id,
                                nav_id: _this.list.nav_id,
                                project_id: _this.projectId,
                                type: _this.list.type
                            });
                            _this.AllNavItems = response.navItems.original.success;
                            $("#updateListBoardModel").modal('hide');
                            Bus.$emit('UpdateListOrBoard');
                            _this.Socket.emit('taskUpdate', {
                                project_id: _this.projectId,
                                list_id: _this.list.id,
                                board_id: _this.selectedData.multiple_board_id,
                                user_id: _this.authUser.id,
                                task_id: _this.selectedData.id
                            });
                            _this.$toastr.i("Successfully Update List");
                        }
                    })
                    .catch(error => {
                        console.log('Add list api not working!!')
                    });
            },
            MoveListTOAnotherNav(data) {
                var type = data.type;
                var _this = this;
                _this.type_T = type;
                _this.list_T = [];
                _this.nav_T = [];
                _this.selectedListNav = 'Select list Nav';
                _this.transferBtn = false;

                axios.get('/api/nav-item/' + _this.projectId)
                    .then(response => response.data)
                    .then(response => {
                        _this.nav_T = response.success;
                        setTimeout(() => {
                            $('#transToNav').modal('show');
                        }, 300);
                        _this.$toastr.s("Successfully moved List !");
                    })
                    .catch(error => {

                    });
            },
            get_T_Bttn() {
                this.transferBtn = true;
            },
            MoveAllTask() {
                var _this = this;
                _this.delete_popup = 1;

                Swal.fire({
                    title: "Are you sure?",
                    text: "You want to delete the list and move all task ?!!!",
                    type: "warning",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "Yes, Delete  & Move Task"
                }).then((result) => {
                    if (result.value) {
                        axios.post('/api/board-list-move', {
                            type: _this.type_T,
                            id: _this.board_id,
                            target: _this.selectedListNav
                        })
                            .then(response => response.data)
                            .then(response => {
                                _this.nav_id = _this.selectedListNav;
                                _this.delete_popup = 0;
                                _this.Socket.emit('task-list-Update', {
                                    project_id: _this.projectId,
                                    list_id: _this.list.id,
                                    user_id: _this.authUser.id
                                });
                                _this.$toastr.s("Successfully moved task !");
                                Bus.$emit('UpdateListOrBoard');
                                setTimeout(() => {
                                    Swal.close();
                                }, 1000);
                            })
                            .catch(error => {
                                console.log('Add list api not working!!')
                            });
                    }
                });
            },
            DeleteListOrBoard(data) {
                var type = data.type;
                var action = data.action;
                var _this = this;
                _this.delete_popup = 1;
                _this.action_T = action;
                _this.type_T = type;
                if (action === 'move') {
                    _this.list_T = [];
                    _this.nav_T = [];
                    _this.selectedListNav = 'Select list Nav';
                    _this.transferBtn = false;
                    axios.get('/api/nav-item/' + _this.projectId)
                        .then(response => response.data)
                        .then(response => {
                            _this.nav_T = response.success;
                            setTimeout(() => {
                                $('#transAndMoveTAsk').modal('show');
                            }, 200);

                        })
                        .catch(error => {

                        });
                } else {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "If you delete this " + type + " then all task will delete !!!",
                        showCancelButton: true,
                        confirmButtonText: "Delete board with all cards!",
                        cancelButtonText: "Cancel",
                        icon: 'warning',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        allowOutsideClick: false
                    }).then((isConfirm) => {
                        if (isConfirm.value) {
                            axios.post('/api/board-list-delete', {type: type, id: _this.board_id, action: action})
                                .then(response => response.data)
                                .then(response => {
                                    _this.delete_popup = 0;
                                    _this.$toastr.s("Board deleted and remove all card successfully !");
                                    Bus.$emit('UpdateListOrBoard');
                                    _this.Socket.emit('DeleteNav', {project_id: _this.projectId,});
                                    this.$router.push({
                                        name: 'Project-OverView',
                                        params: {projectId: _this.projectId, type: 'lists'}
                                    });
                                })
                                .catch(error => {
                                    console.log('Add list api not working!!')
                                });
                        }
                    })

                }

            },
            showSubList_T() {

                let _this = this;
                _this.transferBtn = false;
                _this.list_T = [];
                _this.column_T = [];
                _this.selectedSubList = 'Select list';
                let data = {
                    'projectId': _this.projectId,
                    'listId': _this.selectedListNav,
                    'type': _this.type_T,

                };
                axios.post('/api/multiple-list', data)
                    .then(response => response.data)
                    .then(response => {
                        _this.list_T = response.success;
                        _this.type_T = response.type;
                    })
                    .catch(error => {
                    });
            },
            DeleteAndMoveAllTask() {
                var _this = this;
                _this.delete_popup = 1;
                Swal.fire({
                    title: "Are you sure?",
                    text: "You want to delete the list and move all task ?!!!",
                    type: "warning",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Delete  & Move Task'

                }).then((result) => {
                    if (result.value) {
                        axios.post('/api/board-list-delete', {
                            type: _this.type_T,
                            id: _this.board_id,
                            action: _this.action_T,
                            target: _this.selectedSubList
                        })
                            .then(response => response.data)
                            .then(response => {
                                _this.delete_popup = 0;
                                _this.$toastr.w("This Board is deleted & move successfully !");
                                _this.Socket.emit('DeleteNav', {project_id: _this.projectId,});
                                Bus.$emit('UpdateListOrBoard');
                                _this.$router.push({
                                    name: 'Project-OverView',
                                    params: {projectId: _this.projectId, type: 'lists'}
                                });
                            })
                            .catch(error => {
                                console.log('Add list api not working!!')
                            });
                    }
                })
            },
            ckeditFileUpload() {
                var _this = this;
                CKEDITOR.plugins.get('bbcode');
                let token = Spark.csrfToken;
                _this.editorsDesc = CKEDITOR.replace('editor', {
                    filebrowserUploadUrl: '/api/card-file-upload?type=json',
                    filebrowserUploadMethod: 'xhr',
                    fileTools_requestHeaders: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token
                    }
                });
                _this.editorsDesc.on('fileUploadResponse', function (evt) {
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
            },
        },
        directives: {
            ClickOutside
        },
        watch: {
            '$route.params.id': {
                handler: function (id) {
                    var _this = this;
                    _this.projectId = _this.$route.params.projectId;
                    _this.board_id = _this.$route.params.id;
                    _this.nav_id = _this.$route.params.nav_id;
                    _this.list.name = _this.$route.params.title;
                    _this.list.description = _this.$route.params.description;
                    _this.getBoardTask();
                },
                deep: true,
                immediate: true
            }
        }
    }
</script>


<!-- Notifications Modal -->
<spark-notifications
    :notifications="notifications"
    :has-unread-announcements="hasUnreadAnnouncements"
    :loading-notifications="loadingNotifications" inline-template>

    <div>
        <div class="modal docked docked-right" id="modal-notifications" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-center" style="padding: 10px 0;">
                        <div class="panel-tabs" style="width: 100%;">
                            <ul class="nav nav-tabs nav-float" role="tablist">
                                <li class="text-center nav-item" v-if="notification_type === 0">
                                    <a href="#r_tab1" role="tab" data-toggle="tab" class="nav-link active">
                                        <i class="fa fa-fw fa-bell"></i>
                                    </a>
                                </li>
                                <li class="text-center nav-item" id="getAllComment-2" @click="showComments" v-if="notification_type === 1">
                                    <a href="#r_tab3" role="tab" data-toggle="tab" class="nav-link active">
                                        <i class="fa fa-comments"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="modal-body">
                        <div class="tab-content active">
                            <div  v-if="notification_type === 0">
                                <div id="slim_t1">
                                    <!-- Informational Messages -->
                                    <h5 class="rightsidebar-right-heading text-uppercase text-xs"
                                        style="width: 100%;float: left;margin-top: 70px;clear: both;">
                                        <i class="menu-icon fa fa-fw fa-bell"></i>
                                        Notifications
                                    </h5>
                                    <div class="notification-container" v-if="loadingNotifications"
                                         style="width:100%;float: left;">
                                        <i class="fa fa-btn fa-spinner fa-spin"></i> {{__('Loading Notifications')}}
                                    </div>

                                    <div class="notification-container"
                                         v-if=" ! loadingNotifications && activeNotifications.length == 0"
                                         style="width:100%;float: left;">
                                        <div class="alert alert-warning m-b-none">
                                            {{__('We don\'t have anything to show you right now! But when we do, we\'ll be sure to let you know. Talk to you soon!')}}
                                        </div>
                                    </div>

                                    <!-- List Of Notifications -->
                                    <div class="notification-container" v-if="showingNotifications && hasNotifications">
                                        <div class="notification" v-for="notification in notifications.notifications"
                                             style="width:100%;float: left;word-break: break-all">
                                            <!-- Notification Icon -->
                                            <figure>
                                                <img v-if="notification.creator" :src="notification.creator.photo_url" class="spark-profile-photo">
                                                <span v-else class="fa-stack fa-2x">
                                                    <i class="fa fa-circle fa-stack-2x"></i>
                                                    <i :class="['fa', 'fa-stack-1x', 'fa-inverse', notification.icon]"></i>
                                                </span>
                                            </figure>

                                            <!-- Notification -->
                                            <div class="notification-content">
                                                <div class="meta">
                                                    <p class="title">
                                                        <span v-if="notification.creator">
                                                            @{{ notification.creator.name }}
                                                        </span>
                                                        <span v-else>
                                                            {{ Spark::product() }}
                                                        </span>
                                                    </p>
                                                    <div class="date">
                                                        @{{ notification.created_at | relative }}
                                                    </div>
                                                </div>

                                                <div class="notification-body" v-html="notification.parsed_body"></div>

                                                <!-- Notification Action -->
                                                <a :href="notification.action_url" class="btn btn-primary" v-if="notification.action_text">
                                                    @{{ notification.action_text }}
                                                </a>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div  v-if="notification_type === 1">
                                <div class="notification-container" v-if="comments.length > 0 ">
                                    <div class="notification" v-for="comment in comments"
                                         style="border-bottom: 1px dashed #c7cdd1;border-top: none;margin-bottom: 0;word-break: break-all">
                                        <!-- Notification Icon -->
                                        <figure>
                                            <img v-if="comment.user.photo_url" :src="comment.user.photo_url"
                                                 class="spark-profile-photo">
                                            <span v-else class="fa-stack fa-2x">
                                                    <i class="fa fa-circle fa-stack-2x"></i>
                                                    <i :class="['fa', 'fa-stack-1x', 'fa-inverse']"></i>
                                                </span>
                                        </figure>

                                        <!-- Notification -->
                                        <div class="notification-content">
                                            <div class="meta">
                                                <p class="title">
                                                    <span>
                                                        @{{ comment.user.name }}
                                                    </span>
                                                </p>

                                                <div class="date">
                                                    @{{ comment.created_at | relative }}
                                                </div>
                                            </div>

                                            <div class="notification-body">
                                                    <span v-if="comment.comment != ''">
                                                        <b>Comment on task</b> " @{{ comment.task.title }} " <br> <b>That you are tagged in!</b>

                                                        <p v-html="comment.comment"></p>
                                                        <br>
                                                        <a class="badge badge-primary"
                                                           @click="showSingleViewFromTopVar(comment.task)"
                                                           style="height: 23px;line-height: 20px;z-index: 99999;float: right;color: white">
                                                            View Details
                                                        </a>
                                                    </span>
                                                <span v-else>
                                                    Attached a file on a task you are tagged in! <br>
                                                    <b>@{{ comment.attatchment }}</b>
                                                </span>
                                            </div>
                                            <!-- Notification Action -->
                                            <a class="btn btn-primary" v-if="comment">

                                            </a>

                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    <div class="alert alert-warning m-b-none">
                                        {{__('We don\'t have anything to show you right now! But when we do, we\'ll be sure to let you know. Talk to you soon!')}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default " data-dismiss="modal">{{__('Close')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</spark-notifications>

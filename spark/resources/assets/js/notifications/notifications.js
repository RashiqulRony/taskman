module.exports = {
    props: ['notifications', 'hasUnreadAnnouncements', 'loadingNotifications'],

    /**
     * The component's data.
     */
    data() {
        return {
            showingNotifications: true,
            showingAnnouncements: false,
            comments : [],
            Socket : null,
            notification_type : null,
        }
    },

    mounted(){
        var _this = this;
        _this.connectSocket();
        Bus.$on('showNotifications', function () {
            _this.notification_type = 0;
        });

        Bus.$on('showComment', function () {
            _this.showComments();
            $('#modal-comments').modal('show');
            _this.notification_type = 1;

        });
    },


    methods: {
        connectSocket: function () {
            let app = this;
            if (app.Socket == null) {
                app.Socket = io.connect(window.socket_url);
                app.Socket.on('notification-update', function (res) {
                    // console.log(res)
                })
            }
        },
        /**
         * Show the user notifications.
         */
        showNotifications(data) {
            this.showingNotifications = true;
            this.showingAnnouncements = false;
        },

        showComments(){
            var _this = this;
            var projectId = _this.$route.params.projectId;
            axios.post('/api/get-all-comment-count')
                .then(response => response.data)
                .then(response => {
                    _this.comments = response.comments;
                    Bus.$emit('UpdateCommentCount');
                })
                .catch(error => {
                    _this.$toastr.w('Field To connect!');
                });
        },
        /**
         * Show the product announcements.
         */
        showSingleViewFromTopVar(task) {
            var _this = this;
            var nav_type = JSON.parse(localStorage.selected_nav);
            localStorage.task_view_type = JSON.stringify('comment');
            let routeData = this.$router.resolve({
                name: 'single-task-view',
                params: {projectId: task.project_id, type: 'lists', task_id: btoa(task.id), id: task.list_id != null ? task.list_id : task.multiple_board_id}
            });
            window.open(routeData.href, '_blank');
        },

        showAnnouncements() {
            this.showingNotifications = false;
            this.showingAnnouncements = true;

            this.updateLastReadAnnouncementsTimestamp();
        },


        /**
         * Update the last read announcements timestamp.
         */
        updateLastReadAnnouncementsTimestamp() {
            axios.put('/user/last-read-announcements-at')
                .then(() => {
                    Bus.$emit('updateUser');
                });
        },

        redirectTo(url){
            window.location.href = url;
        }
    },


    computed: {
        /**
         * Get the active notifications or announcements.
         */
        activeNotifications() {
            if ( ! this.notifications) {
                return [];
            }

            if (this.showingNotifications) {
                return this.notifications.notifications;
            } else {
                return this.notifications.announcements;
            }
        },


        /**
         * Determine if the user has any notifications.
         */
        hasNotifications() {
            return this.notifications && this.notifications.notifications.length > 0;
        },


        /**
         * Determine if the user has any announcements.
         */
        hasAnnouncements() {
            return this.notifications && this.notifications.announcements.length > 0;
        }
    }
};


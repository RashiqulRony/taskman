<template>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <div class="row">
                        <div class="col text-left">Comment Panel</div>
                        <div class="col mr-4 text-right" style="display: none;">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="comment_block">
                        <div class="new_comment" v-if="All_comments != null && All_comments.length > 0" style="height: 400px; overflow-x: hidden; overflow-y: auto">
                            <template v-for="comment in All_comments">
                                <ul class="user_comment">
                                    <div class="user_avatar">
                                        <img :src="comment.user.photo_url" v-if="comment.user.photo_url !== null">

                                        <p class="comment-avature user_avatar" v-else>
                                            {{comment.user.name.substring(0,2)}}
                                        </p>
                                    </div>
                                    <div class="comment_body">
                                        <p>
                                            <span class="user">{{comment.user.name}} :</span>

                                            <span class="commentDetails" v-if="comment.attatchment === null" v-html="comment.comment"></span>
                                            <template v-else>
                                                <a :href="baseUrl+'/storage/'+comment.task_id+'/comment/'+comment.attatchment" data-toggle="tooltip" title="Click For View Attachment" target="_blank">
                                                    <img v-if="comment.attatchment.toLowerCase().endsWith('.png') || comment.attatchment.toLowerCase().endsWith('.jpg') || comment.attatchment.toLowerCase().endsWith('.jpeg')" :src="baseUrl+'/storage/'+comment.task_id+'/comment/'+comment.attatchment" height="100" alt="">
                                                    <span v-else>{{comment.attatchment}}</span>
                                                </a>
                                            </template>
                                        </p>
                                        <p>
                                            <span style="cursor: pointer; color: #6495ED" @click="ShowList(comment.list_id)"></span>
                                            Task : {{comment.task.title}}
                                            <a class="badge badge-primary" @click="showSingleViewFromTopVar(comment.task)" style="height: 23px;line-height: 20px;z-index: 99999;float: right;color: white">
                                                View Details
                                            </a>
                                        </p>
                                    </div>

                                    <div class="comment_toolbar">
                                        <div class="comment_details">
                                            <ul>
                                                <li>
                                                    <i class="fa fa-clock-o"></i> {{comment.created_at | relative}}
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
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "comment",
        data() {
            return {
                All_comments: {},
            }
        },

        mounted() {
            this.GetAllComments();
        },

        methods: {
            GetAllComments() {
                let _this = this;
                axios.get('/api/get-comments-for-user')
                    .then(response => response.data)
                    .then(response => {
                        _this.All_comments = response.comments;
                        setTimeout(function () {
                            $('[data-toggle="tooltip"]').tooltip();
                        }, 500)
                    })
                    .catch(error => {
                        console.log('Add files api not working!!')
                    });
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
        }
    }
</script>

<style scoped>
    .card-header.bg-secondary {
        background: #174991 !important
    }
</style>

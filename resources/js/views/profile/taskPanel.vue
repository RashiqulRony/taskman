<template>
    <div class="row" v-if="(dueTaskLists.length !== 0 && dueTaskListsWithPriority.length !== 0)">
        <div class="col-12">
            <div class="card">
                <div class="card-body" style="padding-top: 1px;padding-left: 5px;">
                    <div class="row ml-2">
                        <div class="col-12">
                            <div class="panel-tabs" style="width: 100%;">
                                <ul class="nav nav-tabs nav-float" role="tablist">
                                    <li class="nav-item text-center" v-if="dueTaskLists.length !== 0">
                                        <a href="#r_tab1"
                                           role="tab"
                                           data-toggle="tab"
                                           class="nav-link"
                                           @click="getDueTask"
                                        > Due Date Task Panel </a>
                                    </li>

                                    <li class="nav-item text-center">
                                        <a href="#r_tab2"
                                           role="tab"
                                           data-toggle="tab"
                                           class="nav-link"
                                           @click="getDueTaskWithPriority"
                                        > Priority Task Panel </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-content">
                                <div id="r_tab1" v-if="dueTaskLists.length !== 0" :class="{'tab-pane': true, fade: true, in:true}">
                                    <div>
                                        <ul class="list-group"
                                            v-for="dueTaskList in dueTaskLists"
                                            :key="dueTaskList.id"
                                        >
                                            <li class="list-group-item active mt-4">
                                                {{ dueTaskList.name }}
                                            </li>
                                            <li v-for="task in dueTaskList.tasks"
                                                class="list-group-item"
                                                :key="task.id"
                                            >
                                                {{ task.title }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div id="r_tab2" :class="{'tab-pane': true, fade: true, in:true}">
                                    <div>
                                        <div class="pt-0">
                                            <ul class="list-group"
                                                v-for="dueTaskList in dueTaskListsWithPriority"
                                                :key="dueTaskList.id"
                                            >
                                                <li class="list-group-item active mt-4">
                                                    {{ dueTaskList.name }}
                                                </li>
                                                <li v-for="task in dueTaskList.tasks"
                                                    class="list-group-item"
                                                    :key="task.id"
                                                >
                                                    <div>
                                                        <div class="float-left">
                                                            <span>{{ task.title }}</span>
                                                        </div>
                                                        <div class="float-right">
                                                            <span class=" dropdown-toggle-split"
                                                                  v-if="task.priority_label !== null"
                                                                  style="top: 12px;"
                                                                  data-toggle="dropdown"
                                                            >
                                                            </span>
                                                            <span v-if="task.priority_label === '3'"
                                                                  class="badge badge-danger">High</span>
                                                            <span v-if="task.priority_label === '1'"
                                                                  class="badge badge-primary">Low</span>
                                                            <span v-if="task.priority_label === '2'"
                                                                  class="badge badge-warning">Medium</span>
                                                            <span>{{ dateFormat(task.date) }}</span>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        components: {},
        data() {
            return {
                dueTaskLists: {},
                dueTaskListsWithPriority: {}
            };
        },
        props: [],
        mounted() {
            this.getDueTask();
            this.getDueTaskWithPriority();
        },
        methods: {
            dateFormat(value) {
                return moment
                    .utc(value)
                    .local()
                    .format("D MMM");
            },
            getDueTask() {
                let _this = this;
                axios
                    .get("/api/get-weekly-due-tasks/")
                    .then(response => response.data)
                    .then(response => {
                        _this.dueTaskLists = response.data;
                        console.log(_this.dueTaskLists.length);
                        if(_this.dueTaskLists.length > 0){
                            $('[href="#r_tab1"]').addClass('active');
                            $('#r_tab1').addClass(' in active show');
                        } else {
                            $('[href="#r_tab2"]').addClass('active');
                            $('#r_tab2').addClass(' in active show');
                        }
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            getDueTaskWithPriority() {
                let _this = this;
                axios
                    .get("/api/get-priority-task")
                    .then(response => response.data)
                    .then(response => {
                        console.log(response.data);
                        _this.dueTaskListsWithPriority = response.data;
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        }
    };
</script>
<style scoped>
    .nav-tabs .nav-link.active,
    .nav-tabs .nav-item.show .nav-link {
        color: #ffffff;
        background-color: #174991 !important;
        border-color: #dee2e6 #dee2e6 #fff;
    }

    .list-group {
        margin-left: 0 !important;
    }

    .list-group-item {
        position: relative;
        display: block;
        padding: 1rem 1.25rem !important;
        background-color: #fff;
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .list-group-item.active {
        z-index: 2;
        color: #fff;
        background-color: #6699cc;
        border-color: #6699cc;
    }

    .badge {
        font-size: 12px;
        margin-right: 25px;
    }
</style>

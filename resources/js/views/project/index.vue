import Swal from "sweetalert2";
<template>
    <aside class="right-aside">
        <div class="container">
            <section class="content">
                <div class="row user-list">
                    <div class="col-lg-12">
                        <div class="card bg-primary-card">
                            <div class="card-header">
                                <h2>
                                    <i class="fa fa-fw fa-tasks"></i> Projects
                                    <router-link :to="{ name: 'project-create',params: { action_type : 'create' }}">
                                        <button class="btn btn-primary" style="float: right;margin-right: 20px;"
                                                type="submit"> Create Project
                                        </button>
                                    </router-link>
                                </h2>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" data-v-095ab3dc="" style="padding: 15px 10px; height: calc(100vh - 150px);">
                                    <table class="table" data-v-095ab3dc="">
                                        <thead class="save-all">
                                        <tr data-v-095ab3dc="">
                                            <th class="sortable " style="width: auto;">
                                                #
                                            </th>
                                            <th class="sortable " data-v-095ab3dc="" style="width: auto;">
                                                Title <i class="fa float-right" data-v-095ab3dc=""></i></th>
                                            <th class="sortable" data-v-095ab3dc="" style="width: auto;">
                                                Description <i class="fa float-right" data-v-095ab3dc=""></i></th>
                                            <th class="sortable" data-v-095ab3dc="" style="width: auto;">
                                                Status <i class="fa float-right" data-v-095ab3dc=""></i></th>
                                            <th class="sortable" data-v-095ab3dc=""
                                                style="width: auto;text-align: right">
                                                Actions <i class="fa float-right" data-v-095ab3dc=""></i></th>
                                        </tr>
                                        </thead>
                                        <tbody data-v-095ab3dc="">
                                        <tr v-for="(project,key) in projects">
                                            <td>
                                                <span>{{key+1}}</span>
                                            </td>
                                            <td @click="projectView(project.id,project.name)">
                                                <span class="compltit-blue-link">{{project.name}}</span>
                                            </td>
                                            <td v-text="project.description ? project.description : ''"></td>
                                            <td>Active</td>
                                            <td class="table-option" style="text-align: right;">
                                                <template v-if="project.created_by === auth_id">
                                                    <a @click.prevent="editProject(project)"
                                                       class="compltit-blue-a badge badge-info"
                                                       href="javascript:void(0)" data-toggle="tooltip"
                                                       title="Edit Project">
                                                        <i data-v-0ca4b43b="" class="fal fa-edit"
                                                           aria-hidden="true"></i>
                                                    </a>
                                                    <a :key="project.id" @click.prevent="deleteProject(project)"
                                                       class="compltit-blue-a badge badge-danger"
                                                       href="javascript:void(0)" data-toggle="tooltip"
                                                       title="Delete Project">
                                                        <i class="fal fa-trash-alt" aria-hidden="true"></i>
                                                    </a>
                                                </template>
                                                <template v-else>
                                                    <a class="compltit-blue-a badge badge-default"
                                                       href="javascript:void(0)" data-toggle="tooltip"
                                                       title="You can't edit or delete this project">
                                                        <i class="fad fa-debug"></i>
                                                    </a>
                                                </template>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </aside>
</template>
<script>
    import projectForm from './form'
    import Swal from 'sweetalert2';

    export default {
        components: {projectForm},
        data() {
            return {
                projects: {},
                filterProjectForm: {
                    name: '',
                    sortBy: 'name',
                    order: 'asc',
                },
                showFilterPanel: false,
                auth_id : null
            };
        },
        mounted() {
            $('#header-item').text('Projects');
            // this.projectView();

            this.getProjects();
        },
        methods: {

            projectView(id, name) {
                let _this = this;
                localStorage.browser_last_project = JSON.stringify(id);
                this.$router.push({name: 'Project-OverView', params: {projectId: id,type : 'lists'}});

            },
            getProjects() {
                axios.get('/api/project')
                    .then(response => response.data)
                    .then(response => {
                        this.projects = response.Projects;
                        this.auth_id = response.user_id;
                        setTimeout(function () {
                            $('[data-toggle="tooltip"]').tooltip();
                        }, 200)
                    })
                    .catch(error => {

                    });
            },
            editProject(project) {
                this.$router.push('/project/' + project.id + '/edit');
            },
            confirmDelete(project) {
                return dialog => this.deleteProject(project);
            },
            deleteProject(project) {
                var _this = this;
                Swal.fire({
                    title: "Are you sure?",
                    text: "If you delete this project, all data will deleted.",
                    type: "warning",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "Yes, delete them",
                }).then((result) => {
                    if (result.value) {
                        axios.get('/api/project-delete/' + project.id)
                            .then(response => response.data)
                            .then(response => {
                                _this.getProjects();
                            }).catch(error => {

                        });
                        Swal.close();
                    }
                })
            }
        },
        watch: {
            filterProjectForm: {
                handler(val) {
                    this.getProjects();
                },
                deep: true
            }
        }
    }
</script>

<template>
    <div class="container">
        <div class="col-md-12 ">
            <div class="card card-default border-primary">
                <div class="card-header bg-primary text-white " id="header-item" style="font-weight: bold">
                    {{title}}
                </div>
                <div class="card-body">
                    <form role="form">
                        <!-- Token Name -->
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">Project Title</label>

                            <div class="col-md-6">
                                <input class="form-control" name="name" type="text" v-model="project.title">
                                <span class="text-danger">{{title_error}}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">Project Description</label>

                            <div class="col-md-6">
                                <input class="form-control" name="description" type="text"
                                       v-model="project.description">
                            </div>
                        </div>
                        <div class="form-group row" v-if="action_type === 'create'">
                            <label class="col-md-4 col-form-label text-md-right">Select Template</label>

                            <div class="col-md-6">
                                <select class="form-control" id="sel1" v-model="project.template">
                                    <option v-for="template in templates" :value="template.uid">{{template.name}}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Create Button -->
                        <div class="form-group row mb-0" style="text-align: right">
                            <div class="offset-md-4 col-md-6">
                                <button @click.prevent="addProject" class="btn btn-primary" type="submit"
                                        v-if="id === undefined">Create
                                </button>
                                <button @click.prevent="UpdateProject" class="btn btn-primary" v-else type="submit">
                                    Update
                                </button>
                                <a class="btn btn-default" href="/projects">Back</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Show Token Modal -->
        </div>

        <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="CreateProjectOption"
             role="dialog"
             tabindex="-1">
            <div class="modal-dialog " role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-radius: 13px;">
                        <h5 class="modal-title">Select Option</h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body list-model" style="height: 200px;">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <label class="checkbox_cus_mini">
                                    <input type="radio" class="checkedUser" @click="AddTeamMemberToProject(1)"
                                           :checked="member_option == 1 ? true : false">
                                    Auto add all team members to this project
                                    <span class="checkmark"></span>
                                </label>

                            </li>
                            <li class="list-group-item">
                                <small >
                                    After this project is created you can ADD and REMOVE team members from the project overview
                                </small>
                            </li>
                        </ul>

                    </div>
                    <div class="modal-footer alert-footer ">
                        <button class="btn btn-primary alert-left-icon" v-if="member_option === 1"
                                @click="CreateProject" type="button">
                            Create project with assign all team member
                        </button>
                        <button class="btn btn-info alert-right-icon" v-else
                                @click="CreateProject" type="button">
                            Create private project
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>
<script>

    export default {
        data() {
            return {
                project: {
                    id: '',
                    title: '',
                    description: '',
                    template: 'default'
                },
                title_error: null,
                id: this.$route.params.uuid,
                action_type: this.$route.params.action_type,
                title: null,
                templates: [
                    {uid: 'default', name: 'Blank'},
                    {uid: 'software', name: 'Software Project'},
                    {uid: 'design', name: 'Design Project'},
                    {uid: 'basic ', name: 'Basic Project'},
                    {uid: 'writers', name: 'Writers Project'},
                ],
                member_option : 1

            }
        },
        mounted() {
            var _this = this;
            _this.title_error = null;
            if (this.id !== undefined) {
                this.title = 'Project Edit';
                this.FindProject();
            } else {
                _this.action_type = 'create';
                this.title = 'Project Create';
            }
        },
        methods: {
            addProject() {
                var _this = this;
                _this.title_error = null;
                if (this.project.title.length > 3) {
                    $('#CreateProjectOption').modal('show');
                } else {
                    if (this.project.title.length !== 0) {
                        _this.title_error = "Project title must have minimum 4 character!"
                    }
                    if (this.project.title.length === 0) {
                        _this.title_error = "Project title can't Empty"
                    }
                }

            },
            FindProject() {
                var _this = this;
                axios.get('/api/project/' + _this.id)
                    .then(response => response.data)
                    .then(response => {
                        if (response.success === 1) {
                            var project = response.project;
                            _this.project.id = project.id;
                            _this.project.title = project.name;
                            _this.project.description = project.description;
                        }
                    })
                    .catch(error => {

                    });

            },
            UpdateProject() {
                var _this = this;
                _this.title_error = null;
                if (this.project.title.length > 3) {
                    axios.post('/api/project-update', _this.project)
                        .then(response => response.data)
                        .then(response => {
                            if (response.status === 'success') {
                                window.location.href = "/projects";
                                // console.log(response)
                            } else {
                                _this.title_error = "Project title already taken!"
                            }
                        })
                        .catch(error => {

                        });
                } else {
                    if (_this.project.title.length !== 0) {
                        _this.title_error = "Project title must have minimum 4 character!"
                    }
                    if (_this.project.title.length === 0) {
                        _this.title_error = "Project title can't Empty"
                    }
                }

            },
            AddTeamMemberToProject(type){
                if (this.member_option === type){
                    this.member_option = 0;
                } else {
                    this.member_option = type;
                }
            },
            CreateProject(){
                let _this = this;
                _this.project.member_option = _this.member_option
                axios.post('/api/project', _this.project)
                    .then(response => response.data)
                    .then(response => {
                        if (response.success === 1) {
                            window.location.href = "/projects";
                        } else {
                            _this.title_error = "Project title already taken!"
                        }
                    })
                    .catch(error => {

                    });
            }
        }
    }
</script>

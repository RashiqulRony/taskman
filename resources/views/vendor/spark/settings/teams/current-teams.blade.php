<spark-current-teams :user="user" :teams="teams" inline-template>
    <div>
        <div class="card card-default border-info">
            <div class="card-header bg-primary text-white">{{__('teams.current_teams')}}</div>

            <div class="table-responsive">
                <table class="table table-valign-middle mb-0">
                    <thead>
                    <th class="th-fit"></th>
                    <th>{{__('Name')}}</th>
                    <th>{{__('Owner')}}</th>
                    <th>&nbsp;</th>
                    </thead>

                    <tbody>
                    <tr  v-for="team in teams">
                        <!-- Photo -->
                        <td>
                            <span v-if="user.current_team_id == team.id">
                                <i class="fa fa-fw fa-btn fa-check text-success" style="font-size: 25px;"></i>
                            </span>

                            <span v-else>
                                <img :src="team.photo_url" class="spark-profile-photo" alt="{{__('Team Photo')}}" /><i class="fa fa-btn"></i>
                            </span>
                        </td>

                        <!-- Team Name -->
                        <td>
                            <div class="btn-table-align">
                                <a :href="'/settings/{{Spark::teamsPrefix()}}/'+team.id">
                                    @{{ team.name }}
                                </a>
                            </div>
                        </td>

                        <!-- Owner Name -->
                        <td>
                            <div class="btn-table-align">
                                <span v-if="user.id == team.owner.id">
                                    {{__('You')}}
                                </span>

                                <span v-else>
                                    @{{ team.owner.name }}
                                </span>
                            </div>
                        </td>

                        <!-- Edit Button -->
                        <td class="td-fit">
                            <div class="d-flex">

                                <a v-if="user.current_team_id !== team.id" :href="'{{ url('active-team') }}/'+team.id"
                                   data-toggle="tooltip" title="Active This Team">
                                    <button class="btn  btn-default mr-1">
                                        <i class="fa fa-fw text-left fa-btn fa-check text-success"></i>
                                    </button>
                                </a>

                                <a :href="'/settings/{{Spark::teamsPrefix()}}/'+team.id">
                                    <button class="btn  btn-primary mr-1">
                                        <i class="fa fa-cog"></i>
                                    </button>
                                </a>

                                <button class="btn btn-outline-warning mr-1" @click="approveLeavingTeam(team)"
                                        data-toggle="tooltip" title="{{__('teams.leave_team')}}"
                                        v-if="user.id !== team.owner_id">
                                    <i class="fa fa-sign-out"></i>
                                </button>

                                @if (Spark::createsAdditionalTeams())
                                    <button class="btn btn-danger" @click="approveTeamDelete(team)" v-if="user.id === team.owner_id">
                                        <i class="fa fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Leave Team Modal -->
        <div class="modal" id="modal-leave-team" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="leavingTeam">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{__('teams.leave_team')}} (@{{ leavingTeam.name }})
                        </h5>
                    </div>

                    <div class="modal-body">
                        {{__('teams.are_you_sure_you_want_to_leave_team')}}
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{__('No, Go Back')}}</button>

                        <button type="button" class="btn btn-warning" @click="leaveTeam" :disabled="leaveTeamForm.busy">
                            {{__('Yes, Leave')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Team Modal -->
        <div class="modal" id="modal-delete-team" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="deletingTeam">
                <div class="modal-content border-primary">
                    <div class="modal-header bg-primary text-white rounded-0">
                        <h5 class="modal-title">
                            {{__('teams.delete_team')}}
                        </h5>
                    </div>

                    <div class="modal-body">
                        {{__('teams.are_you_sure_you_want_to_delete_team')}}
                        {{__('teams.if_you_delete_team_all_data_will_be_deleted')}}
                    </div>
                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning text-white mr-1" data-dismiss="modal">{{__('No, Go Back')}}</button>

                        <button type="button" class="btn btn-danger" @click="deleteTeam" :disabled="deleteTeamForm.busy">
                            <span v-if="deleteTeamForm.busy">
                                <i class="fa fa-btn fa-spinner fa-spin"></i> {{__('Deleting')}}
                            </span>

                            <span v-else>
                                {{__('Yes, Delete')}}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</spark-current-teams>

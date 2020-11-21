<!-- Teams -->
<h6 class="dropdown-header">{{ __('teams.teams')}}</h6>

<!-- Switch Current Team -->
@if (Spark::showsTeamSwitcher())
    <a class="dropdown-item selectTeam" v-for="team in teams" :href="'{{ url('team') }}/'+team.id" :data-id="team.id" :data-type="JSON.stringify(team)">
        <span v-if="user.current_team_id == team.id">
            <i class="fa fa-fw text-left fa-btn fa-check text-success"></i> @{{ team.name }}
        </span>

        <span v-else>
            <img :src="team.photo_url" class="spark-profile-photo-xs"
                 alt="{{__('Team Photo')}}" /><i class="fa fa-btn"></i> @{{ team.name }}
        </span>
    </a>
@endif
<!-- Create Team -->
@if (Spark::createsAdditionalTeams())
    <a class="dropdown-item" href="/settings#/{{Spark::teamsPrefix()}}">
        <i class="fa fa-fw text-left fa-btn fa-plus-circle compltit-blue"></i> {{__('teams.create_team')}}
    </a>
@endif
<div class="dropdown-divider"></div>

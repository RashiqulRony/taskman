<li>
    <a href="/projects" id="team-change-click">
        <i class="fal fa-angle-double-right"></i>
        <span class="mm-text ">All Projects</span>
    </a>
</li>
@foreach($projects as $project)
    <li>
        <a href="/project-dashboard/{{$project->id}}/overview/lists">
            <i class="fal fa-angle-right"></i>
            <span class="mm-text ">{{$project->name}}</span>
        </a>
    </li>
@endforeach

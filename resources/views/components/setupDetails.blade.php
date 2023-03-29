
    <li class="nav-item">
        @if(!empty($viewData['client']))
            <a class="nav-link" href="{{ route('step-one.create') }}">| {!! $client->company->Company_Name !!} |</a>
        @else
            <a class="nav-link" href="{{ route('step-one.create') }}">| Set Client |</a>
        @endif
    </li>

    <li class="nav-item">
        @if(!empty($viewData['project']))
        <a class="nav-link" href="{{ route('step-two.create') }}">| {!! $project->Project_Name !!} |</a>
        @else
            <a class="nav-link" href="{{ route('step-two.create') }}">| Set Project |</a>
        @endif
    </li>

    @if(!empty($viewData['client']) && !empty($viewData['project']))
    <li class="nav-item">
        <a class="nav-link" href="{{ route('check.index') }}">| Check Stock</a>
    </li>
    @endif


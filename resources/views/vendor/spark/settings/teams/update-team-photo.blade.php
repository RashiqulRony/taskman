@section('breadcrumb')

@endsection
<spark-update-team-photo :user="user" :team="team" inline-template>
    <div class="card card-default border-primary " v-if="user">
        <div class="card-header bg-primary text-white">
            {{__('teams.team_photo')}}
        </div>

        <div class="card-body">
            <div class="alert alert-danger" v-if="form.errors.has('photo')">
                @{{ form.errors.get('photo') }}
            </div>

            <form role="form">
                <div class="form-group row justify-content-center text-center">
                    <div class="col-md-6  align-items-center">
                        <div class="image-placeholder mx-auto">
                            <span role="img" class="profile-photo-preview" :style="previewStyle"></span>
                        </div>
                        <div class="spark-uploader mt-3">
                            <input ref="photo" type="file" class="spark-uploader-control" name="photo" @change="update" :disabled="form.busy">
                            <div class="btn btn-primary">{{__('UPDATE PHOTO')}}</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</spark-update-team-photo>

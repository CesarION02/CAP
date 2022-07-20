<!-- Modal -->
<div id="scheduleDaysModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Horario:</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-offset-1 col-md-10">
                        <div class="row">
                            <div class="col-md-10">
                                <ul>
                                    <li v-for="day in lScheduleDays">
                                        <div v-if="day.is_active == 1">
                                            @{{day.day_name}}: @{{day.day_entry}} - @{{day.day_departure}}
                                        </div>
                                        <div v-else>
                                            @{{day.day_name}}: No laborable
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    
                </div>
            </div>
        </div>

    </div>
</div>

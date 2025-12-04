<div class="tab-pane" id="tournament" role="tabpanel">
    <div class="kt-section">
        <h3 class="kt-section__title">
            Дней:
        </h3>
        <div class="form-group row">
            <div class="col-lg-4">
                <label>Периодичность подведения итогов турнира</label>
                <input type="text" class="form-control" value="{{ $settings_tournament->days }}" name="tournament[days]" />
            </div>
        </div>
    </div>

    <div class="kt-section">
        <h3 class="kt-section__title">
            Места
        </h3>

        <div class="form-group row">
            @foreach ($settings_tournament->places as $key => $reward)         
                <div class="col-lg-4">
                    <label>Награда за место: {{ $key }}:</label>
                    <input type="text" class="form-control" placeholder="" value="{{ $reward }}" name="tournament[places][{{$key}}]" />
                </div>
            @endforeach
        </div>
    </div>
</div>

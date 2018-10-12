<style>
.md-form label {
    top: auto;
}

.md-form label.active {
    font-size: inherit;
    -webkit-transform: none;
    -moz-transform: none;
    -ms-transform: none;
    -o-transform: none;
    transform: none;
}

.form-group label {
    top: auto;
}
</style>
{START_FORM}
<p>{MENU}</p>
<p>{PURGE}</p>
<div class="md-form">
{DEFAULT_SLOCATION_LABEL} 
{DEFAULT_SLOCATION}</div>

<h3>{DISTANCE_LABEL}</h3>
{MILES_OR_KILOMETERS_1} {MILES_OR_KILOMETERS_1_LABEL}<br />
{MILES_OR_KILOMETERS_2} {MILES_OR_KILOMETERS_2_LABEL}
<p>{CARPOOL} {CARPOOL_LABEL}</p>
<div class="md-form">
{DEFAULT_DESTINATION_LABEL} 
{DEFAULT_DESTINATION}
</div>
<p>{SUBMIT}</p>
{END_FORM}

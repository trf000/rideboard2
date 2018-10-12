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
{LINKS} {START_FORM}
<div class="col-sm-12">
 <div class="row">
  <div class="col-sm-6">
        <h2>{SEARCH_TITLE}</h2>
        <div class="md-form">
        {SEARCH_WORDS_LABEL}
        {SEARCH_WORDS}</div>
        
        <div class="md-form">{SEARCH_S_LOCATION_LABEL}
        {SEARCH_S_LOCATION}</div>
        <div class="md-form">{SEARCH_D_LOCATION_LABEL}
        {SEARCH_D_LOCATION}</div>
        <div class="md-form">{USE_DATE} {USE_DATE_LABEL}</strong> <span
            class="smaller"
        >({NOTE})</span><br />
        <!-- {SEARCH_TIME_MONTH} {SEARCH_TIME_DAY} {SEARCH_TIME_YEAR}
        {SEARCH_JS} -->
        {SEARCH_TIME}
        </div>
        <div class="md-form">{SEARCH_RIDE_TYPE_LABEL}
        {SEARCH_RIDE_TYPE}</div>
        <div class="md-form">{SEARCH_GENDER_PREF_LABEL}
        {SEARCH_GENDER_PREF}</div>
        <div class="md-form">{SEARCH_SMOKING_LABEL}
        {SEARCH_SMOKING}</div>
        {SEARCH_RIDE}
   </div>
   <div class="col-sm-6">

        <h2>{POST_TITLE}</h2>
        <div class="md-form">{TITLE_LABEL}
        {TITLE}</div>
        <div class="md-form">{S_LOCATION_LABEL}
        {S_LOCATION}</div>
        <div class="md-form">{D_LOCATION_LABEL}
        {D_LOCATION}</div>
        <div class="md-form">{DEPART_TIME_LABEL}
        <!-- {DEPART_TIME_MONTH} {DEPART_TIME_DAY} {DEPART_TIME_YEAR}
        {DEPART_JS} -->
        {DEPART_TIME} 
        </div>
        <div class="md-form">{RIDE_TYPE_LABEL}
        {RIDE_TYPE}</div>
        <div class="md-form">{GENDER_PREF_LABEL}
        {GENDER_PREF}</div>
        <div class="md-form">{SMOKING_LABEL}
        {SMOKING}</div>
        <div class="md-form">{COMMENTS_LABEL}
        {COMMENTS}</div>
        {POST_RIDE}
        </div>
</div>
</div>
{END_FORM}

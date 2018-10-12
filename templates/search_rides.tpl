{LINK}
<br />
<br />
{TOTAL_ROWS}
<table class="table table-striped table-bordered">
    <tr>
        <th>{TITLE_SORT} {TITLE_LABEL}</th>
        <th>{RIDE_TYPE_SORT} <abbr title="{RIDE_TYPE_LABEL}">{RIDE_TYPE_ABBR}</abbr></th>
        <th>{GENDER_PREF_SORT} {GEN_PREF_LABEL}</th>
        <th>{SMOKING_SORT} {SMOKE_LABEL}</th>
        <th>{START_LOCATION_SORT} {START_LABEL}</th>
        <th>{DEST_LOCATION_SORT} {DEST_LABEL}</th>
        <th>&nbsp;</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr>
        <td>{TITLE}</td>
        <td>{RIDE_TYPE}</td>
        <td>{GENDER_PREF}</td>
        <td>{SMOKING}</td>
        <td>{START_LOCATION}<br />
        {DEPART_TIME}</td>
        <td>{DEST_LOCATION}</td>
        <td>{ADMIN_LINKS}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div align="center"><strong>{PAGE_LABEL}</strong><br />
{PAGES}<br />
{LIMITS}</div>

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
<div style="text-align: right">{SEARCH}</div>
{LINKS}
<p>{TOTAL_ROWS} | {LINK}</p>
<table class="table table-striped table-bordered">
    <tr>
        <th>{CREATED_SORT}</th>
        <th>{START_ADDRESS_SORT}</th>
        <th>{DEST_ADDRESS_SORT}</th>
        <th>&#160;</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr class="{TOGGLE}">
        <td>{CREATED}</td>
        <td>{START_ADDRESS}</td>
        <td>{DEST_ADDRESS}</td>
        <td>{LINKS}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div align="center"><b>{PAGE_LABEL}</b><br />
{PAGES}<br />
{LIMITS}</div>

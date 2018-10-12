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
<div class="md-form">{START_ADDRESS_LABEL}
{START_ADDRESS}</div>
<div class="md-form">{DEST_ADDRESS_LABEL}
{DEST_ADDRESS}</div>
<div class="md-form">{COMMENT_LABEL}
{COMMENT}</div>
{SUBMIT} {END_FORM}

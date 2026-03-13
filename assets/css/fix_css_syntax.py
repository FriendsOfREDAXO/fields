
import os

file_path = "/Users/thomas/redaxo_instances/core/project/public/redaxo/src/addons/fields/assets/css/fields-backend.css"

# The new CSS with corrected syntax (separating media queries)
# + enforced square borders
new_css = """/* Switch Toggle BASE */
.fields-inline-switch {
    width: 36px;
    height: 20px;
    background-color: #e6e6e6; /* Light gray base */
    border-radius: 0 !important; /* <--- SQUARE (HARD) */
    position: relative;
    cursor: pointer;
    transition: background-color 0.2s ease;
    display: inline-block;
    vertical-align: middle;
}
.fields-inline-switch .fields-switch-slider {
    width: 16px;
    height: 16px;
    background-color: #fff;
    border-radius: 0 !important; /* <--- SQUARE (HARD) */
    position: absolute;
    top: 2px;
    left: 2px;
    transition: left 0.2s cubic-bezier(0.4, 0.0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.fields-inline-switch.fields-switch-active {
    background-color: #5bb585; /* <--- NEW GREEN */
}
.fields-inline-switch.fields-switch-active .fields-switch-slider {
    left: 18px;
}
.fields-inline-switch.loading {
    opacity: 0.7;
    cursor: wait;
}

/* Improve Dark Mode Colors for Switch (Explicit & Auto) */

/* 1. Explicit Dark Mode Class */
body.rex-theme-dark .fields-inline-switch {
    background-color: rgba(255, 255, 255, 0.15); /* Transparent Gray for Inactive */
    border: 1px solid rgba(255, 255, 255, 0.1);
}
body.rex-theme-dark .fields-inline-switch .fields-switch-slider {
    background-color: rgba(255, 255, 255, 0.7);
    box-shadow: 0 1px 3px rgba(0,0,0,0.5);
}
body.rex-theme-dark .fields-inline-switch.fields-switch-active {
    background-color: rgba(91, 181, 133, 0.8); /* #5bb585 with opacity */
    border-color: transparent;
}
body.rex-theme-dark .fields-inline-switch.fields-switch-active .fields-switch-slider {
    background-color: #fff;
    opacity: 1;
}

/* 2. Auto Dark Mode (Media Query) */
@media (prefers-color-scheme: dark) { 
    body.rex-has-theme:not(.rex-theme-light) .fields-inline-switch {
        background-color: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    body.rex-has-theme:not(.rex-theme-light) .fields-inline-switch .fields-switch-slider {
        background-color: rgba(255, 255, 255, 0.7);
        box-shadow: 0 1px 3px rgba(0,0,0,0.5);
    }
    body.rex-has-theme:not(.rex-theme-light) .fields-inline-switch.fields-switch-active {
        background-color: rgba(91, 181, 133, 0.8);
        border-color: transparent;
    }
    body.rex-has-theme:not(.rex-theme-light) .fields-inline-switch.fields-switch-active .fields-switch-slider {
        background-color: #fff;
        opacity: 1;
    }
}
"""

try:
    with open(file_path, "r") as f:
        lines = f.readlines()

    start_index = -1
    for i, line in enumerate(lines):
        if "/* Switch Toggle BASE */" in line:
            start_index = i
            break

    if start_index != -1:
        lines = lines[:start_index]
        with open(file_path, "w") as f:
            f.writelines(lines)
            f.write(new_css)
        print("Updated Switch CSS with corrected syntax.")
    else:
        with open(file_path, "a") as f:
            f.write("\n" + new_css)
        print("Appended Switch CSS.")

except Exception as e:
    print(f"Error: {e}")

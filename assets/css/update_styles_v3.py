
import os

file_path = "/Users/thomas/redaxo_instances/core/project/public/redaxo/src/addons/fields/assets/css/fields-backend.css"

new_css = """/* Switch Toggle BASE */
.fields-inline-switch {
    width: 36px;
    height: 20px;
    background-color: #e6e6e6; /* Light gray base */
    border-radius: 4px; /* <--- SQUARE CHANGED */
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
    border-radius: 2px; /* <--- SQUARE CHANGED */
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
body.rex-theme-dark .fields-inline-switch,
@media (prefers-color-scheme: dark) { body.rex-has-theme:not(.rex-theme-light) .fields-inline-switch } {
    background-color: rgba(255, 255, 255, 0.15); /* Transparent Gray for Inactive */
    border: 1px solid rgba(255, 255, 255, 0.1);
}

body.rex-theme-dark .fields-inline-switch .fields-switch-slider,
@media (prefers-color-scheme: dark) { body.rex-has-theme:not(.rex-theme-light) .fields-inline-switch .fields-switch-slider } {
    background-color: rgba(255, 255, 255, 0.7); /* Slightly transparent white slider */
    box-shadow: 0 1px 3px rgba(0,0,0,0.5);
}

body.rex-theme-dark .fields-inline-switch.fields-switch-active,
@media (prefers-color-scheme: dark) { body.rex-has-theme:not(.rex-theme-light) .fields-inline-switch.fields-switch-active } {
    background-color: rgba(91, 181, 133, 0.8); /* <--- NEW GREEN GRADATION #5bb585 with 0.8 opacity */
    border-color: transparent;
}

body.rex-theme-dark .fields-inline-switch.fields-switch-active .fields-switch-slider,
@media (prefers-color-scheme: dark) { body.rex-has-theme:not(.rex-theme-light) .fields-inline-switch.fields-switch-active .fields-switch-slider } {
    background-color: #fff; /* Bright white when active */
    opacity: 1;
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
        print("Updated Switch CSS successfully.")
    else:
        # Fallback if marker not found: Append to end
        print("Marker not found, appending to end.")
        with open(file_path, "a") as f:
            f.write("\n" + new_css)

except Exception as e:
    print(f"Error: {e}")

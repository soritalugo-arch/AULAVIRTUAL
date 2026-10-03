
const passwordInput =
    document.getElementById("password");

const togglePassword =
    document.getElementById("togglePassword");

const eyeIcon =
    document.getElementById("eyeIcon");


togglePassword.addEventListener("click", () => {

    const isPassword =
        passwordInput.type === "password";


    passwordInput.type =
        isPassword ? "text" : "password";


    togglePassword.setAttribute(
        "aria-label",
        isPassword
            ? "Ocultar contraseña"
            : "Mostrar contraseña"
    );


    if (isPassword) {

        eyeIcon.innerHTML = `
            <path
                d="M3 20
                   C7 13
                   13 9
                   20 9
                   C27 9
                   33 13
                   37 20
                   C33 27
                   27 31
                   20 31
                   C13 31
                   7 27
                   3 20Z"
                stroke="currentColor"
                stroke-width="2.5"
            />

            <circle
                cx="20"
                cy="20"
                r="5"
                stroke="currentColor"
                stroke-width="2.5"
            />
        `;

    } else {

        eyeIcon.innerHTML = `
            <path
                d="M3 20
                   C7 13
                   13 9
                   20 9
                   C27 9
                   33 13
                   37 20"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
            />

            <path
                d="M37 20
                   C33 27
                   27 31
                   20 31
                   C13 31
                   7 27
                   3 20"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
            />

            <path
                d="M7 7L33 33"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
            />
        `;
    }

});

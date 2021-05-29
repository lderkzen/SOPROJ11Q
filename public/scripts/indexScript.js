const gameInput = document.querySelector('#get_game_input');
const deleteGameForm = document.querySelector('#delete_game_form');
const buttonsBox = document.querySelector('#buttons_box');
const passwordFieldGet = document.querySelector('#password_get_input');
const sideBarItem2 = document.querySelector('#side_bar_link2');
const sideBarItem3 = document.querySelector('#side_bar_link3');

let gameIds = [];
let password_correct;
let game_exists;

function setGameIds(ids) {
    gameIds = ids;
}

setInterval(() => {
    changeNumberInputs(gameIds);
}, 300);

async function changeNumberInputs(gameIds) {
    let openGameButton = document.querySelector('#open_game_button');
    let deleteGameButton = document.querySelector('#delete_game_button');

    if (!game_exists || !password_correct) {
        if (openGameButton && deleteGameButton) {
            buttonsBox.removeChild(openGameButton);
            deleteGameForm.removeChild(deleteGameButton);
            deleteGameForm.action = '';
            sideBarItem2.href = '/games';
            sideBarItem3.href = '/games';
            openGameButton = document.querySelector('#open_game_button');
            deleteGameButton = document.querySelector('#delete_game_button');
        }
    }

    game_exists = false;

    if (gameInput.value && gameInput.value > 0) {
        gameIds.forEach(gameId =>{
            if (gameId == gameInput.value)
                game_exists = true;
        });

        if (!game_exists) {
            return;
        }

        await checkPassword(gameInput.value);

        if (password_correct === false)
            return;

        if (!openGameButton && !deleteGameButton) {
            let openButtonElem = document.createElement('a');
            openButtonElem.id = 'open_game_button';
            openButtonElem.href = '/games/' + gameInput.value;
            let openButtonTextElem = document.createElement('h3');
            openButtonTextElem.textContent = 'Ga naar spel ' + gameInput.value;
            openButtonElem.appendChild(openButtonTextElem);

            let deleteButtonElem = document.createElement('button');
            deleteButtonElem.class = 'config-delete-button';
            deleteButtonElem.id = 'delete_game_button';
            deleteButtonElem.type = 'submit';
            let deleteButtonTextElem = document.createElement('b');
            deleteButtonTextElem.textContent = 'Verwijder spel ' + gameInput.value;
            deleteButtonElem.appendChild(deleteButtonTextElem);

            buttonsBox.insertBefore(openButtonElem, deleteGameForm);
            deleteGameForm.appendChild(deleteButtonElem);
            deleteGameForm.action = '/games/' + gameInput.value;
        }

        sideBarItem2.href = '/games/' + gameInput.value;
        sideBarItem3.href = '/games/' + gameInput.value;
    }
}

async function checkPassword(game_id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    await $.ajax({
        url: '/games/' + game_id + '/password',
        type: 'GET',
        data: { password: passwordFieldGet.value },
        success: function (data) {
            if (data == 1) {
                password_correct = true;
            }
            else {
                password_correct = false;
            }
        },
        error: function (err) {
            console.log(err);
        },
    });
}

function showPassword(actor) {
    if (actor === 'create') {
        let passwordFieldCreate = document.querySelector('#password_create_input');
        passwordFieldCreate.type = switchType(passwordFieldCreate.type);
    }
    else {
        passwordFieldGet.type = switchType(passwordFieldGet.type);
    }
}

function switchType(fromType) {
    if (fromType === 'password') {
        return 'text';
    } else {
        return 'password';
    }
}

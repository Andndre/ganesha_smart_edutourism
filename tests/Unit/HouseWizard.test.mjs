import assert from 'node:assert/strict';
import { test } from 'node:test';
import { hasHouseLocation, houseStepForField } from '../../resources/js/house-wizard.js';

test('coordinates must be complete and within geographic bounds, including zero', () => {
    assert.equal(hasHouseLocation('-8.42345', '115.36542'), true);
    assert.equal(hasHouseLocation('0', '0'), true);
    for (const [lat, lng] of [[null, null], ['', ''], [' ', '115.36'], ['-8.42', ''], ['invalid', '115'], ['91', '115'], ['-8', '181']]) {
        assert.equal(hasHouseLocation(lat, lng), false);
    }
});

test('server errors return the user to the step where they can fix the field', () => {
    assert.equal(houseStepForField('number'), 1);
    assert.equal(houseStepForField('cover_image'), 1);
    assert.equal(houseStepForField('description.en'), 1);
    assert.equal(houseStepForField('latitude'), 2);
    assert.equal(houseStepForField('longitude'), 2);
    assert.equal(houseStepForField('owner_user_id'), 3);
    assert.equal(houseStepForField('new_owner_email'), 3);
    assert.equal(houseStepForField('password_confirmation'), 3);
});

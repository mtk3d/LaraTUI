<?php

use LaraTui\State;

describe('test global app state', function () {
    beforeEach(function () {
        $this->state = new State();
    });

    test('can read stored data', function () {
        $this->state->set('some_key', 'some_data');

        expect($this->state->get('some_key'))->toBe('some_data');
    });

    test('return default value if no value', function () {
        expect($this->state->get('some_key', 'default_value'))
            ->toBe('default_value');
    });

    test('can append to current value', function () {
        $this->state->set('some_key', 'some_');
        $this->state->append('some_key', '_value');

        expect($this->state->get('some_key'))
            ->toBe('some__value');
    });

    test('is destroying object when delete', function () {
        class TestClass {
            public static $destructorCalled = false;

            public function __destruct() {
                self::$destructorCalled = true;
            }
        }

        $this->state->set('some_key', new TestClass());
        $this->state->delete('some_key');

        expect(TestClass::$destructorCalled)->toBeTrue();
    });
});


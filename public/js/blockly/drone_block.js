Blockly.Blocks.drone_takeoff = {
  /**
   * Block for TakeOff.
   * @this Blockly.Block
   */
  init() {
    // ブロックの色を鮮やかにする。最初に設定することで、この後のブロックにも適用される。
    Blockly.HSV_SATURATION = 1;
    Blockly.HSV_VALUE = 0.5;

    // TakeOff の設定
    this.jsonInit({
        "type": "drone_takeoff",
        "message0": Blockly.Msg["DRONE_TAKEOFF"],
        "inputsInline": true,
        "previousStatement": null,
        "nextStatement": null,
        "colour": 315,
        "tooltip": Blockly.Msg["DRONE_TAKEOFF"],
        "helpUrl": ""
    });
  },
};

Blockly.PHP.drone_takeoff = function(block) {
    return "$tello->takeoff();\n";
};

Blockly.Blocks.drone_land = {
  /**
   * Block for Land.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
        "type": "drone_land",
        "message0": Blockly.Msg["DRONE_LAND"],
        "inputsInline": false,
        "previousStatement": null,
        "nextStatement": null,
        "colour": 315,
        "tooltip": Blockly.Msg["DRONE_LAND"],
        "helpUrl": ""
    });
  },
};

Blockly.PHP.drone_land = function(block) {
    return "$tello->land();\n";
};

Blockly.Blocks.drone_up = {
  /**
   * Block for Up.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
      "type": "drone_up",
      "message0": Blockly.Msg["DRONE_UP_EXT"],
      "args0": [
        {
          "type": "field_number",
          "name": "arg_drone_up",
          "value": 0
        }
      ],
      "inputsInline": true,
      "previousStatement": null,
      "nextStatement": null,
      "colour": 230,
      "tooltip": Blockly.Msg["DRONE_UP"],
      "helpUrl": ""
    });
  },
};

Blockly.PHP.drone_up = function(block) {
    arg_drone_up = block.getFieldValue('arg_drone_up');
    return "$tello->up(" + arg_drone_up + ");\n";
};

Blockly.Blocks.drone_down = {
  /**
   * Block for Down.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
      "type": "drone_down",
      "message0": Blockly.Msg["DRONE_DOWN_EXT"],
      "args0": [
        {
          "type": "field_number",
          "name": "arg_drone_down",
          "value": 0
        }
      ],
      "inputsInline": true,
      "previousStatement": null,
      "nextStatement": null,
      "colour": 230,
      "tooltip": Blockly.Msg["DRONE_DOWN"],
      "helpUrl": ""
    });
  },
};

Blockly.PHP.drone_down = function(block) {
    arg_drone_down = block.getFieldValue('arg_drone_down');
    return "$tello->down(" + arg_drone_down + ");\n";
};

Blockly.Blocks.drone_forward = {
  /**
   * Block for Forward.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
      "type": "drone_forward",
      "message0": Blockly.Msg["DRONE_FORWARD_EXT"],
      "args0": [
        {
          "type": "field_number",
          "name": "arg_drone_forward",
          "value": 0
        }
      ],
      "inputsInline": true,
      "previousStatement": null,
      "nextStatement": null,
      "colour": 180,
      "tooltip": Blockly.Msg["DRONE_FORWARD"],
      "helpUrl": ""
    });
  },
};

Blockly.PHP.drone_forward = function(block) {
    arg_drone_forward = block.getFieldValue('arg_drone_forward');
    return "$tello->forward(" + arg_drone_forward + ");\n";
};

Blockly.Blocks.drone_back = {
  /**
   * Block for Back
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
      "type": "drone_back",
      "message0": Blockly.Msg["DRONE_BACK_EXT"],
      "args0": [
        {
          "type": "field_number",
          "name": "arg_drone_back",
          "value": 0
        }
      ],
      "inputsInline": true,
      "previousStatement": null,
      "nextStatement": null,
      "colour": 180,
      "tooltip": Blockly.Msg["DRONE_BACK"],
      "helpUrl": ""
    });
  },
};

Blockly.PHP.drone_back = function(block) {
    arg_drone_back = block.getFieldValue('arg_drone_back');
    return "$tello->back(" + arg_drone_back + ");\n";
};

Blockly.Blocks.drone_right = {
  /**
   * Block for Right
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
      "type": "drone_right",
      "message0": Blockly.Msg["DRONE_RIGHT_EXT"],
      "args0": [
        {
          "type": "field_number",
          "name": "arg_drone_right",
          "value": 0
        }
      ],
      "inputsInline": true,
      "previousStatement": null,
      "nextStatement": null,
      "colour": 180,
      "tooltip": Blockly.Msg["DRONE_RIGHT"],
      "helpUrl": ""
    });
  },
};

Blockly.PHP.drone_right = function(block) {
    arg_drone_right = block.getFieldValue('arg_drone_right');
    return "$tello->right(" + arg_drone_right + ");\n";
};

Blockly.Blocks.drone_left = {
  /**
   * Block for Left.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
      "type": "drone_left",
      "message0": Blockly.Msg["DRONE_LEFT_EXT"],
      "args0": [
        {
          "type": "field_number",
          "name": "arg_drone_left",
          "value": 0
        }
      ],
      "inputsInline": true,
      "previousStatement": null,
      "nextStatement": null,
      "colour": 180,
      "tooltip": Blockly.Msg["DRONE_LEFT"],
      "helpUrl": ""
    });
  },
};

Blockly.PHP.drone_left = function(block) {
    arg_drone_left = block.getFieldValue('arg_drone_left');
    return "$tello->left(" + arg_drone_left + ");\n";
};

Blockly.Blocks.drone_cw = {
  /**
   * Block for Right turn.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
      "type": "drone_cw",
      "message0": Blockly.Msg["DRONE_CW_EXT"],
      "args0": [
        {
          "type": "field_number",
          "name": "arg_drone_cw",
          "value": 0
        }
      ],
      "inputsInline": true,
      "previousStatement": null,
      "nextStatement": null,
      "colour": 90,
      "tooltip": Blockly.Msg["DRONE_CW"],
      "helpUrl": "",
    });
  },
};

Blockly.PHP.drone_cw = function(block) {
    arg_drone_cw = block.getFieldValue('arg_drone_cw');
    return "$tello->cw(" + arg_drone_cw + ");\n";
};

Blockly.Blocks.drone_ccw = {
  /**
   * Block for Left turn.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
      "type": "drone_ccw",
      "message0": Blockly.Msg["DRONE_CCW_EXT"],
      "args0": [
        {
          "type": "field_number",
          "name": "arg_drone_ccw",
          "value": 0
        }
      ],
      "inputsInline": true,
      "previousStatement": null,
      "nextStatement": null,
      "colour": 90,
      "tooltip": Blockly.Msg["DRONE_CCW"],
      "helpUrl": ""
    });
  },
};

Blockly.PHP.drone_ccw = function(block) {
    arg_drone_ccw = block.getFieldValue('arg_drone_ccw');
    return "$tello->ccw(" + arg_drone_ccw + ");\n";
};

Blockly.Blocks.drone_flip = {
  /**
   * Block for Flip.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
      "type": "drone_flip",
      "message0": Blockly.Msg["DRONE_FLIP_EXT"],
      "args0": [
        {
          "type": "field_dropdown",
          "name": "arg_drone_flip",
          "options": [
            [Blockly.Msg["DRONE_FLIP_FOWARD"],"f"],
            [Blockly.Msg["DRONE_FLIP_BACK"],"b"],
            [Blockly.Msg["DRONE_FLIP_RIGHT"],"r"],
            [Blockly.Msg["DRONE_FLIP_LEFT"],"l"]
          ]
        }
      ],
      "inputsInline": true,
      "previousStatement": null,
      "nextStatement": null,
      "colour": 30,
      "tooltip": Blockly.Msg["DRONE_FLIP"],
      "helpUrl": "",
    });
  },
};

Blockly.PHP.drone_flip = function(block) {
    arg_drone_flip = block.getFieldValue('arg_drone_flip');
    return "$tello->flip(" + arg_drone_flip + ");\n";
};

Blockly.Blocks.drone_loop = {
  /**
   * Block for Loop.
   * @this Blockly.Block
   */
  init() {
    this.jsonInit({
        "type": "drone_loop",
        "message0": "%1 回 %2 繰り返し %3",
        "args0": [
            {
                "type": "field_number",
                "name": "arg_drone_loop",
                "value": 0
            },
            {
                "type": "input_dummy"
            },
            {
                "type": "input_statement",
                "name": "loop_statement",
                "check": "Number"
            }
        ],
        "previousStatement": null,
        "nextStatement": null,
        "colour": 230,
        "tooltip": "",
        "helpUrl": ""
    });
  },
};

Blockly.PHP.drone_loop = function(block) {
    arg_drone_loop = block.getFieldValue('arg_drone_loop');
    var statements_name_loop = Blockly.PHP.statementToCode(block, 'loop_statement');
    var php_code = "for ($i = 0; $i < " + arg_drone_loop + "; $i++) {\n";
    php_code = php_code + statements_name_loop;
    php_code = php_code + "}";
    return php_code + ";\n";
};


//Blockly.Blocks.drone_test = {
//  /**
//   * Block for Land.
//   * @this Blockly.Block
//   */
//  init() {
//    this.jsonInit({
//		"type": "drone_test",
//		"message0": "テスト",
//		"previousStatement": null,
//		"nextStatement": null,
//		"colour": 230,
//		"tooltip": "",
//		"helpUrl": ""
//	});
//  },
//};


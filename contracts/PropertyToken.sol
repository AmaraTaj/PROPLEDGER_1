// SPDX-License-Identifier: MIT
pragma solidity ^0.8.9;

import "@openzeppelin/contracts/token/ERC20/ERC20.sol";
import "@openzeppelin/contracts/access/Ownable.sol";

contract PropertyToken is ERC20, Ownable {
    constructor(
        string memory name,
        string memory symbol,
        uint256 totalShares,
        address ownerAddr
    ) ERC20(name, symbol) {
        _mint(ownerAddr, totalShares * 1 ether); // 1 token = 1 share
        _transferOwnership(ownerAddr);
    }
}

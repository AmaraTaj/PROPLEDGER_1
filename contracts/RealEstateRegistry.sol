// SPDX-License-Identifier: UNLICENSED
pragma solidity ^0.8.9;

import "@openzeppelin/contracts/utils/ReentrancyGuard.sol";


contract RealEstateRegistry is ReentrancyGuard {
    enum Status { NONE, LISTED, SOLD, FRACTIONALIZED, UNLISTED }

    struct Property {
        uint256 id;
        address payable owner;
        uint256 price; // price in wei for full sale
        string title;
        string category;
        string images; // ipfs/hash or URL
        string propertyAddress;
        string description;
        Status status;
    }

    mapping(uint256 => Property) private properties;
    uint256 public propertyCount;

    // optional external contract addresses
    address public fractionalContract;
    address public crowdfundingContract;

    event PropertyListed(uint256 indexed id, address indexed owner, uint256 price);
    event PropertyUpdated(uint256 indexed id);
    event PropertyUnlisted(uint256 indexed id);
    event PropertySold(uint256 indexed id, address indexed oldOwner, address indexed newOwner, uint256 price);
    event PropertyFractionalized(uint256 indexed id, address fractionalToken);

    modifier onlyOwnerOf(uint256 id) {
        require(properties[id].owner == payable(msg.sender), "Not owner");
        _;
    }

    // set external helpers (optional)
    function setFractionalContract(address _addr) external {
        // in prod make this onlyOwner/admin — omitted for brevity
        fractionalContract = _addr;
    }

    function setCrowdfundingContract(address _addr) external {
        crowdfundingContract = _addr;
    }

    function listProperty(
        uint256 price,
        string memory title,
        string memory category,
        string memory images,
        string memory _propertyAddress,
        string memory description
    ) external returns (uint256) {
        require(price > 0, "Price > 0");
        uint256 id = propertyCount;
        properties[id] = Property({
            id: id,
            owner: payable(msg.sender),
            price: price,
            title: title,
            category: category,
            images: images,
            propertyAddress: _propertyAddress,
            description: description,
            status: Status.LISTED
        });
        propertyCount++;
        emit PropertyListed(id, msg.sender, price);
        return id;
    }

    function updateProperty(
        uint256 id,
        uint256 price,
        string memory title,
        string memory category,
        string memory images,
        string memory _propertyAddress,
        string memory description
    ) external onlyOwnerOf(id) {
        Property storage p = properties[id];
        require(p.status != Status.SOLD, "Already sold");
        p.price = price;
        p.title = title;
        p.category = category;
        p.images = images;
        p.propertyAddress = _propertyAddress;
        p.description = description;
        emit PropertyUpdated(id);
    }

    function unlistProperty(uint256 id) external onlyOwnerOf(id) {
        Property storage p = properties[id];
        p.status = Status.UNLISTED;
        emit PropertyUnlisted(id);
    }

    // full direct buy (not fractional)
    function buyProperty(uint256 id) external payable nonReentrant {
        Property storage p = properties[id];
        require(p.status == Status.LISTED, "Not for sale");
        require(msg.value >= p.price, "Insufficient funds");
        require(msg.sender != p.owner, "Owner cannot buy");

        address payable oldOwner = p.owner;
        uint256 amount = msg.value;

        // transfer funds to seller
        (bool sent,) = oldOwner.call{value: amount}("");
        require(sent, "Payment failed");

        // transfer ownership
        p.owner = payable(msg.sender);
        p.status = Status.SOLD;

        emit PropertySold(id, oldOwner, msg.sender, amount);
    }

    // called by fractional contract when fractionalized
    function markFractionalized(uint256 id) external returns (bool) {
        require(msg.sender == fractionalContract, "Only fractional contract");
        Property storage p = properties[id];
        p.status = Status.FRACTIONALIZED;
        emit PropertyFractionalized(id, msg.sender);
        return true;
    }

    // getters
    function getProperty(uint256 id) external view returns (Property memory) {
        return properties[id];
    }

    function getAllProperties() external view returns (Property[] memory) {
        Property[] memory list = new Property[](propertyCount);
        for (uint256 i = 0; i < propertyCount; i++) {
            list[i] = properties[i];
        }
        return list;
    }

    function getUserProperties(address user) external view returns (Property[] memory) {
        uint256 count = 0;
        for (uint256 i = 0; i < propertyCount; i++) {
            if (properties[i].owner == user) count++;
        }
        Property[] memory out = new Property[](count);
        uint256 j = 0;
        for (uint256 i = 0; i < propertyCount; i++) {
            if (properties[i].owner == user) {
                out[j] = properties[i];
                j++;
            }
        }
        return out;
    }
}

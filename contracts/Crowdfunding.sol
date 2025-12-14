// SPDX-License-Identifier: UNLICENSED
pragma solidity ^0.8.9;

import "@openzeppelin/contracts/utils/ReentrancyGuard.sol";


contract Crowdfunding is ReentrancyGuard {
    struct Campaign {
        uint256 id;
        uint256 propertyId;
        address payable creator; // usually property owner or dev
        uint256 goal;
        uint256 pledged;
        uint256 deadline; // unix timestamp
        bool claimed;
    }

    mapping(uint256 => Campaign) public campaigns;
    mapping(uint256 => mapping(address => uint256)) public contributions; // campaignId -> user -> amount
    uint256 public campaignCount;

    event CampaignCreated(uint256 indexed id, uint256 propertyId, address indexed creator, uint256 goal, uint256 deadline);
    event ContributionMade(uint256 indexed id, address indexed contributor, uint256 amount);
    event Refunded(uint256 indexed id, address indexed contributor, uint256 amount);
    event GoalClaimed(uint256 indexed id, address indexed creator, uint256 amount);

    function createCampaign(uint256 propertyId, uint256 goal, uint256 durationSeconds) external returns (uint256) {
        require(goal > 0, "Goal>0");
        uint256 id = campaignCount;
        campaigns[id] = Campaign({
            id: id,
            propertyId: propertyId,
            creator: payable(msg.sender),
            goal: goal,
            pledged: 0,
            deadline: block.timestamp + durationSeconds,
            claimed: false
        });
        campaignCount++;
        emit CampaignCreated(id, propertyId, msg.sender, goal, block.timestamp + durationSeconds);
        return id;
    }

    function contribute(uint256 campaignId) external payable {
        Campaign storage c = campaigns[campaignId];
        require(block.timestamp <= c.deadline, "Campaign ended");
        require(msg.value > 0, "Must send funds");

        c.pledged += msg.value;
        contributions[campaignId][msg.sender] += msg.value;
        emit ContributionMade(campaignId, msg.sender, msg.value);
    }

    function claim(uint256 campaignId) external nonReentrant {
        Campaign storage c = campaigns[campaignId];
        require(msg.sender == c.creator, "Not creator");
        require(block.timestamp > c.deadline, "Still running");
        require(c.pledged >= c.goal, "Goal not reached");
        require(!c.claimed, "Already claimed");

        c.claimed = true;
        uint256 amount = c.pledged;
        (bool sent,) = c.creator.call{value: amount}("");
        require(sent, "Transfer failed");
        emit GoalClaimed(campaignId, c.creator, amount);
    }

    function refund(uint256 campaignId) external nonReentrant {
        Campaign storage c = campaigns[campaignId];
        require(block.timestamp > c.deadline, "Campaign still running");
        require(c.pledged < c.goal, "Goal reached, cannot refund");

        uint256 contributed = contributions[campaignId][msg.sender];
        require(contributed > 0, "No contribution");

        contributions[campaignId][msg.sender] = 0;
        (bool sent,) = msg.sender.call{value: contributed}("");
        require(sent, "Refund failed");
        emit Refunded(campaignId, msg.sender, contributed);
    }
}

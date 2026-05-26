const express = require('express');
const Ticket = require('../ModelTicket');
const { protect, admin } = require('../MiddlewareAuth');
const router = express.Router();

// Get all tickets (admin) or user's tickets
router.get('/', protect, async (req, res) => {
  try {
    const query = req.user.role === 'admin' ? {} : { user: req.user._id };
    
    const tickets = await Ticket.find(query)
      .populate('user', 'username email')
      .sort({ createdAt: -1 });
    
    res.json(tickets);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
});

// Create ticket
router.post('/', protect, async (req, res) => {
  try {
    const ticket = new Ticket({
      ...req.body,
      user: req.user._id
    });
    
    const createdTicket = await ticket.save();
    await createdTicket.populate('user', 'username');
    
    res.status(201).json(createdTicket);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
});

// Get single ticket
router.get('/:id', protect, async (req, res) => {
  try {
    const ticket = await Ticket.findOne({
      _id: req.params.id,
      $or: [{ user: req.user._id }, { 'user.role': 'admin' }]
    }).populate('user', 'username');
    
    if (!ticket) {
      return res.status(404).json({ message: 'Ticket not found' });
    }
    
    res.json(ticket);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
});

// Update ticket
router.put('/:id', protect, async (req, res) => {
  try {
    const ticket = await Ticket.findOne({
      _id: req.params.id,
      $or: [{ user: req.user._id }, { 'user.role': 'admin' }]
    });
    
    if (!ticket) {
      return res.status(404).json({ message: 'Ticket not found' });
    }
    
    Object.assign(ticket, req.body);
    ticket.updatedAt = new Date();  
    
    const updatedTicket = await ticket.save();
    await updatedTicket.populate('user', 'username');
    
    res.json(updatedTicket);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
});

// Delete ticket (admin only)
router.delete('/:id', protect, admin, async (req, res) => {
  try {
    const ticket = await Ticket.findById(req.params.id);
    if (!ticket) {
      return res.status(404).json({ message: 'Ticket not found' });
    }
    
    await ticket.remove();
    res.json({ message: 'Ticket deleted' });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
});

module.exports = router;
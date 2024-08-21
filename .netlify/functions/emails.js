// netlify/functions/contact.js

const nodemailer = require('nodemailer');

exports.handler = async function(event, context) {
  // Enable CORS headers
  const headers = {
    "Access-Control-Allow-Origin": "*",
    "Access-Control-Allow-Methods": "POST, OPTIONS",
    "Access-Control-Allow-Headers": "Content-Type, Authorization",
    "Access-Control-Allow-Credentials": "true",
  };


  if (event.httpMethod === 'OPTIONS') {
    return {
      statusCode: 200,
      headers: headers,
      body: JSON.stringify({ message: 'CORS preflight check successful.' }),
    };
  }

  // Custom error handling
  const errorHandler = (error) => {
    console.error(`Error: ${error.message}`);
    return {
      statusCode: 500,
      headers: headers,
      body: JSON.stringify({ message: 'An error occurred, please try again later.' }),
    };
  };

  // Logging input data for debugging
  console.log('Received input:', event.body);

  try {
    // Parse JSON input from frontend
    const input = JSON.parse(event.body);

    // Validate input
    if (!input.name || !input.email || !input.message) {
      throw new Error('Missing required fields');
    }

    const { name, email, message } = input;

    // Configure Nodemailer
    const transporter = nodemailer.createTransport({
      service: 'gmail',
      auth: {
        user: process.env.SMTP_USERNAME,
        pass: process.env.SMTP_PASSWORD,
      },
    });

    const mailOptions = {
      from: process.env.SMTP_USERNAME,
      to: process.env.SMTP_USERNAME,
      subject: 'New Contact Form Submission',
      html: `Name: ${name}<br>Email: ${email}<br>Message: ${message}`,
    };

    // Send email
    await transporter.sendMail(mailOptions);

    return {
      statusCode: 200,
      headers: headers,
      body: JSON.stringify({ message: 'Message has been sent' }),
    };
  } catch (error) {
    return errorHandler(error);
  }
};
